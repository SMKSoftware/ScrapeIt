<?php
namespace SmkSoftware\ScrapeIt\Source;

use SmkSoftware\ScrapeIt\Request;

class ArrayUrlSource extends Source
{

    const URLS = 'urls';

    public function __construct($urls = null)
    {
        $this->settings['urls'] = $urls;
        $this->format = "array";
    }

    public function get()
    {
        foreach ($this->settings[self::URLS] as $url) {
            yield new Request('GET', $url);
        }
    }
}
