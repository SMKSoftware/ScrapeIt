<?php

namespace SmkSoftware\ScrapeIt\Matcher;

class KeywordMatcher implements IStringMatcher
{
    private $keywords;
    private $useOr;
    private $ignoreCase;

    /**
     * @param array[]|string $keywords
     * @param bool $useOr
     * @param bool $ignoreCase
     */
    public function __construct($keywords, $useOr = false, $ignoreCase = true)
    {
        $this->useOr = $useOr;
        $this->ignoreCase = $ignoreCase;

        if (is_array($keywords))
            $this->keywords = $keywords;
        else
            $this->keywords = [$keywords];
    }

    public function isMatch($value)
    {
        foreach ($this->keywords as $keyword) {
            $found = $this->ignoreCase ? stripos($value, $keyword) : strpos($value, $keyword);
            if ($this->useOr && $found) return true;
            if (!$this->useOr && !$found) return false;
        }
        return true;
    }
}