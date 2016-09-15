<?php
namespace SmkSoftware\ScrapeIt\StateStorage;

use SmkSoftware\ScrapeIt\Request;
use SmkSoftware\ScrapeIt\Settings;
use SmkSoftware\ScrapeIt\Source\Source;

abstract class StateStorage
{

    /** @var Source[] */
    private $urlSources;

    /** @var Settings */
    protected $settings;

    /*
        public function getItem($key) {
            return $this->urls[$key];
        }
    */
    public function addUrlSource($urlSource)
    {
        $this->urlSources[] = $urlSource;
    }

    public abstract function complete(Request $request);

    private function getUrlSourceGenerator()
    {
        if (!$this->urlSources) return;
        foreach ($this->urlSources as $urlSource) {
            foreach ($urlSource->get() as $request) {
                yield $request;
            }
        }
    }

    private $urlSourceGenerator = null;

    /**
     * @return Request|null
     */
    private function getFromUrlSource()
    {
        if ($this->urlSourceGenerator == null)
            $this->urlSourceGenerator = $this->getUrlSourceGenerator();

        if ($this->urlSourceGenerator->valid()) {
            $value = $this->urlSourceGenerator->current();
            $this->urlSourceGenerator->next();
            return $value;
        }
        return null;
    }

    public abstract function next();

    public final function get()
    {
        do {
            foreach ($this->next() as $key => $value) {
                yield $key => $value;
            }

            $request = $this->getFromUrlSource();
            if ($request) {
                $key = strval($request);
                //$this->onGoingRequests[$key] = $request;
                yield $key => $request;
            }
        } while ($request);
    }

    public function init(Settings $settings = null)
    {
        $this->settings = $settings;
    }

    public function destroy()
    {

    }

    public abstract function add(Request $request, $checkExistence = true, $prepend = false);

}