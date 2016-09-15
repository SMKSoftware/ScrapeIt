<?php
namespace SmkSoftware\ScrapeIt\Client;

use GuzzleHttp;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\Promise;
use SmkSoftware\ScrapeIt\Response;
use SmkSoftware\ScrapeIt\ScrapeClient;
use SmkSoftware\ScrapeIt\Settings;
use SmkSoftware\ScrapeIt\StateStorage\StateStorage;

class SingleThreadClient extends HttpClient
{
    private $pendingRequests = [];

    /** @var Promise */
    private $poolPromise;

    public function afterRequest($index, $response, $reason = null)
    {
        $request = $this->pendingRequests[$index];
        unset($this->pendingRequests[$index]);

        $response = new Response($request, $response);
        $afterRequestCallback = $this->afterRequestCallback;
        $afterRequestCallback($response);
    }

    /*
        public function sendAsync(RequestInterface $request, array $options = [])
        {
            $this->pendingRequests[strval($request)] = $request;
            $beforeRequestCallback = $this->beforeRequestCallback;
            $beforeRequestCallback($request, $options);

            return parent::sendAsync($request, $options);
        }
    */
    public function run(Settings $settings, StateStorage $urlStorage)
    {
        $requests = function (StateStorage $urlStorage) {
            foreach ($urlStorage->get() as $key => $request) {

                $this->pendingRequests[$key] = $request;
                $beforeRequestCallback = $this->beforeRequestCallback;
                if ($beforeRequestCallback($request)) yield $key => $request;
                if ($this->stop) break;
            }
        };

        $pool = new Pool($this, $requests($urlStorage), [
            'concurrency' => $settings->getSetting(ScrapeClient::SET_CONCURRENCY),
            'fulfilled' => function ($response, $index) {
                $this->afterRequest($index, $response);
            },
            'rejected' => function ($reason, $index) {
                $this->afterRequest($index, null, $reason);
            }
        ]);
        $this->poolPromise = $pool->promise();
        $this->poolPromise->wait();
    }
}
