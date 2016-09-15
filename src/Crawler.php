<?php
namespace SmkSoftware\ScrapeIt;


class Crawler extends Symfony\Component\DomCrawler\Crawler
{

    /**
     * Synonim of filterXPath
     * @param $selector - XPath selector
     * @return Crawler
     */
    public function xpath($selector)
    {
        return $this->filterXPath($selector);
    }

    /**
     * Synonim of filter
     * @param $selector
     * @return Crawler
     */
    public function css($selector)
    {
        return $this->css($selector);
    }

    public function texts()
    {
        return $this->each(function (self $element) {
            return $element->text();
        });
    }

    /**
     * Filter elements using xpath selector
     * @param string $xpath
     * @return Crawler
     */
    public function filterXPath($xpath)
    {
        return parent::filterXPath($xpath);
    }

    /**
     * @param array|string|null $attributes
     * @return array
     */
    public function extract($attributes = null)
    {
        if (!$attributes) {
            return $this->each(function (self $element) {
                //if ($element->)
                return $element->html();
            });
        }

        if (is_array($attributes))
            return parent::extract($attributes);

        return $this->each(function (self $element) use ($attributes) {
            return $element->attr($attributes);
        });
    }

    /**
     * Filter elements using css selector
     * @param string $selector
     * @return Crawler
     */
    public function filter($selector)
    {
        return parent::filter($selector);
    }
}