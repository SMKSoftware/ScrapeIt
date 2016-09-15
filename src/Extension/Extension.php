<?php

namespace SmkSoftware\ScrapeIt\Extension;


use Psr\Log\LoggerInterface;
use SmkSoftware\ScrapeIt\Request;
use SmkSoftware\ScrapeIt\Response;
use SmkSoftware\ScrapeIt\ResultItem;
use SmkSoftware\ScrapeIt\ScrapeClient;
use SmkSoftware\ScrapeIt\Settings;
use SmkSoftware\ScrapeIt\Utils;

class Extension
{
    const SET_PRIORITY = 'priority';
    const SET_NAME = 'name';

    /** @return LoggerInterface */
    public function logger()
    {
        return ScrapeClient::getLogger();
    }

    protected $name;
    protected $priority;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
        $this->priority = $settings->getSetting(self::SET_PRIORITY, 1);
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function clientOpened(ScrapeClient $client)
    {
    }

    public function clientClosed(ScrapeClient $client)
    {
    }

    public function itemScraped(ResultItem $item, Response $response, ScrapeClient $client)
    {
    }

    public function beforeRequest(Request $request, ScrapeClient $client)
    {
    }

    public function afterRequest(Response $response, ScrapeClient $client)
    {
    }

    /**
     * @param Settings $settings
     * @return Extension
     */
    public static function create(Settings $settings)
    {
        $name = $settings->getSetting(self::SET_NAME);

        $obj = Utils::createClass($name, [$settings], Extension::class);
        if ($obj === Utils::CREATECLASS_ERROR_NOT_INSTANCE_OF) {
            // TODO: Error
            return null;
        } elseif ($obj === Utils::CREATECLASS_ERROR_NOT_FOUND) {
            // TODO: Error
            return null;
        }
        return $obj;
    }
}