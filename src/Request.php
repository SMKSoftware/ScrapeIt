<?php
namespace SmkSoftware\ScrapeIt;

use SmkSoftware\ScrapeIt\Rule\UrlRule;

class Request extends \GuzzleHttp\Psr7\Request
{
    /** @var callable */
    public $callback;

    /** @var UrlRule */
    public $rule;

    private $string;

    public $params;

    public function __construct($method, $uri, array $headers = [], $body = null, $version = '1.1', $callback = null, $params = [])
    {
        $this->callback = $callback;
        $this->params = $params;
        parent::__construct($method, $uri, $headers, $body, $version);
    }

    public function __toString()
    {
        if (!$this->string) $this->string = $this->getMethod() . $this->getUri();
        return $this->string;
    }

}