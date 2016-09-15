<?php
namespace SmkSoftware\ScrapeIt\Rule;

use SmkSoftware\ScrapeIt\Matcher\IMatcher;

class UrlRule
{
    private $matcher;
    public $parseUrls;
    public $follow;
    /** @var callable */
    public $callback;

    public function __construct(IMatcher $matcher = null, $follow = true, $parse = true, $callback = null)
    {
        $this->matcher = $matcher;
        $this->follow = $follow;
        $this->parseUrls = $parse;
        $this->callback = $callback;
    }

    public function isMatch($url)
    {
        $this->url = $url;
        if (!$this->matcher) return true;
        return $this->matcher->isMatch($url) ? true : false;
    }
}
