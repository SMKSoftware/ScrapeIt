<?php
namespace SmkSoftware\ScrapeIt;


use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Psr7;


class Response
{
    public $request;
    public $response;

    public function __construct(Request $request, \GuzzleHttp\Psr7\Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /** @var Crawler */
    public $domCrawler;

    private function getDomCrawler()
    {
        if ($this->domCrawler == null) $this->domCrawler = new Crawler($this->response->getBody()->getContents());
        return $this->domCrawler;
    }

    public function getUrls()
    {
        foreach ($this->getDomCrawler()->filterXPath('//a/@href | //script/@src | //link/@href') as $url) {
            $resolvedUrl = strval(Psr7\Uri::resolve($this->request->getUri(), $url->value));
            yield $resolvedUrl;
        }
    }

    /** @return Crawler */
    public function filter($selector)
    {
        return $this->getDomCrawler()->filter($selector);
    }

    /** @return Crawler */
    public function filterXPath($selector)
    {
        return $this->getDomCrawler()->filterXPath($selector);
    }

    /** @return Crawler */
    public function xpath($selector)
    {
        return $this->filterXPath($selector);
    }

    /** @return Crawler */
    public function css($selector)
    {
        return $this->filter($selector);
    }
}