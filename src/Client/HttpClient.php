<?php
namespace SmkSoftware\ScrapeIt\Client;

use SmkSoftware\ScrapeIt\Settings;
use SmkSoftware\ScrapeIt\StateStorage\StateStorage;
use GuzzleHttp;

abstract class HttpClient extends GuzzleHttp\Client
{
    /** @var callable */
    protected $afterRequestCallback;

    /** @var callable */
    protected $beforeRequestCallback;

    protected $stop = false;

    public abstract function run(Settings $settings, StateStorage $urlStorage);

    public function onAfterRequest(callable $callback)
    {
        $this->afterRequestCallback = $callback;
    }

    public function onBeforeRequest(callable $callback)
    {
        $this->beforeRequestCallback = $callback;
    }

    public function stop()
    {
        $this->stop = true;
    }
}
