<?php
namespace SmkSoftware\ScrapeIt\Source;

use Generator;
use Psr\Log\LoggerInterface;
use SmkSoftware\ScrapeIt\ScrapeClient;
use SmkSoftware\ScrapeIt\Settings;

abstract class Source
{

    /** @var Settings */
    public $settings;

    const FILE_NAME = 'fileName';
    const TYPE_NAME = 'type';

    /** @var string Name of this source */
    public $name;

    /** @return Generator */
    public abstract function get();

    /** @var string Name of source format */
    protected $format;

    /** @return LoggerInterface */
    public function logger()
    {
        return ScrapeClient::getLogger();
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function init()
    {

    }

    public function destroy()
    {

    }

    public static function create($settings)
    {
        $class = isset($settings[self::TYPE_NAME]);

        $fullClassName = $class . 'UrlSource';

        $obj = null;
        if (class_exists($fullClassName)) {
            $obj = new $fullClassName();
        } elseif (class_exists($class)) {
            $obj = new $class();
        }
        if ($obj || $obj instanceof self) {
            /** @var Source $obj */
            unset($settings[self::TYPE_NAME]);
            $obj->settings->setSettings($settings);
            return $obj;
        }
        return null;
    }
}
