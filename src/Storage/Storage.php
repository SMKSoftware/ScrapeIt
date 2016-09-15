<?php
namespace SmkSoftware\ScrapeIt\Storage;

use Psr\Log\LoggerInterface;
use SmkSoftware\ScrapeIt\ResultItem;
use SmkSoftware\ScrapeIt\ScrapeClient;
use SmkSoftware\ScrapeIt\Settings;

abstract class Storage
{
    /** @var Settings */
    public $settings;

    /** @var string */
    public $name;

    /** @var string */
    public $resultItemClass;

    /** @var string */
    protected $format;

    /** @param mixed $item */
    public abstract function add(ResultItem $item);

    /** @return LoggerInterface */
    public function logger()
    {
        return ScrapeClient::getLogger();
    }

    public function init()
    {

    }

    public function destroy()
    {

    }

    public function getFormat()
    {
        return $this->format;
    }


}