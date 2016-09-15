<?php
namespace SmkSoftware\ScrapeIt\Matcher;

interface IStringMatcher
{
    /**
     * @param $value string
     * @return array|bool
     */
    public function isMatch($value);
}