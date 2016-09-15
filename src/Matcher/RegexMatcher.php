<?php
namespace SmkSoftware\ScrapeIt\Matcher;

class RegexMatcher implements IStringMatcher
{
    private $expression;

    public function __construct($expression)
    {
        $this->expression = $expression;
    }

    public function isMatch($value)
    {
        $matches = null;
        if (preg_match($this->expression, $value, $matches))
            return $matches;
        else
            return false;
    }
}