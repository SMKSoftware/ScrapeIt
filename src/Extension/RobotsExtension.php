<?php
namespace SmkSoftware\ScrapeIt\Extension;

use SmkSoftware\ScrapeIt\Request;
use SmkSoftware\ScrapeIt\ScrapeClient;

class RobotsExtension extends Extension
{
    const SET_SCRAPER_NAME = 'name';
    const SET_CHECK_EACH_REQUESTS = 'checkRequests';
    const SET_CHECK_EACH_TIME = 'checkTime';

    private $requestsCount = 0;
    private $lastCheckTime = 0;

    private $checkEachRequests;
    private $checkEachTime;

    public function beforeRequest(Request $request, ScrapeClient $client)
    {

        //      if (!$this->lastCheckTime) $this->requestRobots();


//            $this->checkEachTime $this->requestsCount==0 || $this->requestsCount)
//        $this->requestsCount++;


        //    $this->client->prependRequest(new Request('GET',new Uri($request->getU)));
    }


}