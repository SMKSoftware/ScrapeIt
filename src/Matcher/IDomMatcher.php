<?php
namespace SmkSoftware\ScrapeIt\Matcher;

interface IDomMatcher extends IMatcher
{
    /**
     * @param $value
     * @return array|bool
     */
    public function isMatch($value);
}