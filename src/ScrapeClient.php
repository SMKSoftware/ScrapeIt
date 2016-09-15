<?php
namespace SmkSoftware\ScrapeIt;

use GuzzleHttp;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use SmkSoftware\ScrapeIt\Client\HttpClient;
use SmkSoftware\ScrapeIt\Client\SingleThreadClient;
use SmkSoftware\ScrapeIt\Extension\Extension;
use SmkSoftware\ScrapeIt\Rule\UrlRule;
use SmkSoftware\ScrapeIt\Source\ArrayUrlSource;
use SmkSoftware\ScrapeIt\Source\Source;
use SmkSoftware\ScrapeIt\StateStorage\MemoryStateStorage;
use SmkSoftware\ScrapeIt\StateStorage\StateStorage;
use SmkSoftware\ScrapeIt\Storage\CsvStorage;
use SmkSoftware\ScrapeIt\Storage\Storage;


class ScrapeClient
{
    const SET_CONCURRENCY = 'concurrency';
    const SET_START_URLS = 'startUrls';
    const SET_SOURCES = "sources";
    const SET_EXTENSIONS = 'extensions';
    const SET_STATE_STORAGE = 'stateStorage';

    const SET_INPUT = 'input';
    const SET_INPUT_FORMAT = 'inputFormat';
    const SET_OUTPUT = 'output';
    const SET_OUTPUT_FORMAT = 'outputFormat';

    /** @var Settings */
    public $settings;

    protected $startUrls = [];

    protected $defaultSettings = [self::SET_CONCURRENCY => 1];

    protected $defaultRule = null;

    /** @var Storage[] */
    private $resultStorages = [];

    /** @var StateStorage */
    private $stateStorage;

    /** @var Source[] */
    private $urlSources = [];

    /** @var UrlRule[] */
    private $rules = [];

    /** @var callable[] */
    private $beforeRequestCallbacks = [];

    /** @var callable[] */
    private $afterRequestCallbacks = [];

    /** @var HttpClient */
    private $client;

    /** @var Extension[] */
    private $extensions = [];

    /** @var LoggerInterface */
    private static $logger;

    private $stop = false;

    // Flow control

    public function stop()
    {
        $this->stop = true;
    }

    public function addRequest(Request $request, $checkExistence = true, $prepend = false)
    {
        $this->stateStorage->add($request, $checkExistence, $prepend);
    }

    // Initialization

    public function init()
    {
    }

    public function addRule(UrlRule $rule)
    {
        $this->rules[] = $rule;

    }

    public function setDefaultRule(UrlRule $rule)
    {
        $this->defaultRule = $rule;
    }

    public function loadSettings($fileName = './Settings.php')
    {
        $this->settings->load($fileName);
    }

    /** @return LoggerInterface */
    public function logger()
    {
        return self::$logger;
    }

    /** @return LoggerInterface */
    public static function getLogger()
    {
        return self::$logger;
    }

    public function initLogger()
    {
        self::$logger = new Logger('');
        // TODO: implement logging configuration!
        //self::$logger->pushHandler(new PHPConsoleHandler());
    }

    public function __construct($settings = null, $client = null)
    {
        $this->settings = new Settings($this->defaultSettings);
        if ($settings) $this->settings->setSettings($settings);

        $this->initLogger();

        if (!$client)
            $this->client = new SingleThreadClient();
        else
            $this->client = $client;

        $this->stateStorage = new MemoryStateStorage();
        $this->defaultRule = new UrlRule(null, true, true, 'parse');
    }

    /**
     * @param string $url
     * @return UrlRule
     */
    private function getRule($url)
    {
        foreach ($this->rules as $rule) {
            if ($rule->isMatch($url)) {
                return $rule;
            }
        }
        return $this->defaultRule;
    }

    public function addUrlSource(Source $source, $name = null)
    {
        if ($name) $source->name = $name;
        $this->urlSources[] = $source;
    }

    private function initSources()
    {
        $input = $this->settings->getSetting(self::SET_INPUT);
        $inputFormat = $this->settings->getSetting(self::SET_INPUT_FORMAT);

        $sources = [];
        // Example: --input example.csv -input-format csv
        if ($input && $inputFormat) {
            if (file_exists($input)) {
                // Set filename setting if we already have this type of source
                foreach ($this->urlSources as $urlSource) {
                    if ($urlSource->getFormat() == $inputFormat) {
                        $urlSource->settings->setSetting(Source::FILE_NAME, $input);
                        $sources[] = $urlSource;
                    }
                }
            } else {
                $this->logger()->error("Input file does not exist", ['input' => $input, 'inputFormat' => $inputFormat]);
            }
            if (!$sources) {
                // Create new source and get settings fot it
                $sourceSettings = $this->settings->getSettings(self::SET_SOURCES)->setSettings([Source::TYPE_NAME => $inputFormat, Source::FILE_NAME => $input]);
                $sources[] = Source::create($sourceSettings);
            }
        } elseif ($input) {
            // Example: --input exampleSource
            foreach ($this->urlSources as $urlSource) {
                if ($urlSource->name == $input) {
                    $sources[] = $urlSource;
                }
            }
        } elseif ($inputFormat) {
            // Example: --input-format csv
            foreach ($this->urlSources as $urlSource) {
                if ($urlSource->getFormat() == $inputFormat) {
                    $sources[] = $urlSource;
                }
            }
        } elseif ($this->startUrls) {
            $sources[] = new ArrayUrlSource($this->startUrls);
        }

        if (!$sources) {
            $this->logger()->error('No url source selected', ['settings' => $this->settings]);
        }
        $this->urlSources = $sources;
        foreach ($this->urlSources as $source)
            $this->stateStorage->addUrlSource($source);

        foreach ($this->urlSources as $urlSource)
            $urlSource->init();
    }

    private function initExtensions()
    {
        $this->settings->getSettings(self::SET_EXTENSIONS)->each(function (Settings $extSettings) {
            $extension = Extension::create($extSettings);
            if ($extension) {
                $this->extensions[$extension->getPriority()] = $extension;
            } else {
                $this->logger()->error("can't initialize extension", ['settings' => $extSettings]);
                $this->stop();
            }
        });
        ksort($this->extensions);
        foreach ($this->extensions as $extension)
            $extension->clientOpened($this);
    }

    private function initStorages()
    {
        $output = $this->settings->getSetting(self::SET_OUTPUT);
        $outputFormat = $this->settings->getSetting(self::SET_OUTPUT_FORMAT);

        $storages = [];

        if ($outputFormat) {
            // Example --output filename.csv --output-format csv
            foreach ($this->resultStorages as $storage) {
                if ($storage->getFormat() == $outputFormat) {
                    if ($output) $storage->settings->setSetting(Source::FILE_NAME, $output);
                    $storages[] = $storage;
                }
            }
        } elseif ($output && !$outputFormat) {
            foreach ($this->resultStorages as $storage) {
                if ($storage->name == $output) $storages[] = $storage;
            }
        } else
            $storages = $this->resultStorages;

        if (!$storages) {
            // TODO: replace to JsonStorage when it will be implemented
            $storages[] = new CsvStorage();
        }

        $this->resultStorages = $storages;

        foreach ($this->resultStorages as $storage)
            $storage->init();
    }

    public function run()
    {
        $this->init();
        $this->stateStorage->init($this->settings->getSettings(self::SET_STATE_STORAGE));
        $this->initStorages();
        $this->initSources();
        $this->initExtensions();

        $this->startUrls = $this->settings->getSetting(self::SET_START_URLS, $this->startUrls);

        $this->client->onAfterRequest(function (Response $response) {
            self::logger()->info('Request completed', ['response' => $response]);

            foreach ($this->extensions as $extension)
                $extension->afterRequest($response, $this);
            if ($this->stop)
                $this->client->stop();

            foreach ($this->afterRequestCallbacks as $callback)
                $callback($response);

            if (!$this->executeRequestCallback($response) || $response->request->rule->parseUrls)
                $this->parseUrls($response);
        });

        $this->client->onBeforeRequest(function (Request $request) {
            $url = $request->getUri();

            foreach ($this->extensions as $extension)
                $extension->beforeRequest($request, $this);
            if ($this->stop) $this->client->stop();

            self::logger()->info('Request completed', ['request' => $request]);
            $rule = $this->getRule($url);
            if (!$rule) {
                $rule = $this->defaultRule;
            }
            $request->rule = $rule;

            foreach ($this->beforeRequestCallbacks as $callback)
                $callback($request);
            // TODO: Check rules?
        });


        $this->client->run($this->settings, $this->stateStorage);

        foreach ($this->urlSources as $urlSource)
            $urlSource->destroy();

        foreach ($this->resultStorages as $storage)
            $storage->destroy();

        foreach ($this->extensions as $extension)
            $extension->clientClosed($this);

        $this->stateStorage->destroy();
    }

    private function executeRequestCallback(Response $response)
    {
        $request = $response->request;

        $callback = $request->callback;
        if (!$callback && $request->rule != null && $request->rule->callback != null)
            $callback = $request->rule->callback;

        if ($callback && method_exists($this, $callback)) {
            $resultGen = $this->$callback($response);
            if ($resultGen) {
                foreach ($resultGen as $result) {
                    if ($result instanceof Request) {
                        $this->stateStorage->add($result);
                    } elseif ($result instanceof ResultItem) {
                        $storage = $this->getStorageForClass($result);
                        if ($storage) $storage->add($result);

                        foreach ($this->extensions as $extension)
                            $extension->itemScraped($result, $response, $this);
                        if ($this->stop) $this->client->stop();

                        if ($this->stop) return true;
                    }
                    // TODO: store strings and ints
                }
            }
            return true;
        }
        return false;
    }

    private function getStorageForClass($item)
    {
        $storage = null;
        if (is_object($item)) {
            $className = get_class($item);
            foreach ($this->resultStorages as $storage) {
                if ($storage->resultItemClass == $className || end(explode('\\', $className)))
                    return $storage;
            }
        }
        if (!$storage && isset($this->resultStorages['']))
            return $this->resultStorages[''];

        return null;
    }

    public function addStorage(Storage $storage, $resultItemClass = '', $name = '')
    {
        $storage->resultItemClass = $resultItemClass;
        $storage->name = $name;
        $this->resultStorages[] = $storage;
    }

    private function parseUrls(Response $response)
    {
        foreach ($response->getUrls() as $url) {

            $rule = $this->getRule($url);
            if (!$rule) continue;

            if ($rule->follow) {
                $request = new Request('GET', $url);
                $this->stateStorage->add($request);
            }
        }
    }

    public function addBeforeRequestEvent($callback)
    {
        $this->beforeRequestCallbacks[] = $callback;
    }

    public function addAfterRequestEvent($callback)
    {
        $this->afterRequestCallbacks[] = $callback;
    }

}
