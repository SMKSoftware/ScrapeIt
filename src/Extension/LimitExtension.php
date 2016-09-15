<?php
/**
 * Created by PhpStorm.
 * User: Администратор
 * Date: 13.09.2016
 * Time: 14:52
 */

namespace SmkSoftware\ScrapeIt\Extension;


use SmkSoftware\ScrapeIt\Request;
use SmkSoftware\ScrapeIt\Response;
use SmkSoftware\ScrapeIt\ResultItem;
use SmkSoftware\ScrapeIt\ScrapeClient;
use SmkSoftware\ScrapeIt\Settings;

class LimitExtension extends Extension
{
    const SET_REQUESTS_LIMIT = 'requests';
    const SET_ITEMS_LIMIT = 'items';

    private $requestsCount = 0;
    private $itemsCount = 0;

    private $requestsLimit;
    private $itemsLimit;

    public function __construct(Settings $settings)
    {
        parent::__construct($settings);
        $this->requestsLimit = $this->settings->getSetting(self::SET_REQUESTS_LIMIT, 0);
        $this->itemsLimit = $this->settings->getSetting(self::SET_ITEMS_LIMIT, 0);
    }

    public function beforeRequest(Request $response, ScrapeClient $client)
    {
        if ($this->requestsLimit && $this->requestsCount > $this->requestsLimit) {
            $this->logger()->info('Stopped by request limit!', [self::SET_REQUESTS_LIMIT => $this->requestsLimit]);
            $client->stop();
        }

        $this->requestsCount++;
    }

    public function itemScraped(ResultItem $item, Response $response, ScrapeClient $client)
    {
        $this->itemsCount++;

        if ($this->itemsLimit && $this->itemsCount >= $this->itemsLimit) {
            $this->logger()->info('Stopped by item limit!', [self::SET_ITEMS_LIMIT => $this->itemsLimit]);
            $client->stop();
        }
    }
}