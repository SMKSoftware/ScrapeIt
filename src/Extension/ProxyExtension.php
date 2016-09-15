<?php
/**
 * Created by PhpStorm.
 * User: Администратор
 * Date: 13.09.2016
 * Time: 10:22
 */

namespace SmkSoftware\ScrapeIt\Extension;

use SmkSoftware\ScrapeIt\Request;
use SmkSoftware\ScrapeIt\ScrapeClient;

class ProxyExtension extends Extension
{
    const SET_LIST = 'list';

    public function beforeRequest(Request $request, ScrapeClient $client)
    {

    }
}