<?php
/**
 * Created by PhpStorm.
 * User: Администратор
 * Date: 12.09.2016
 * Time: 13:24
 */

namespace SmkSoftware\ScrapeIt;


class Settings
{
    protected $settings = [];

    public function __construct($settings = [])
    {
        $this->settings = $settings;
    }

    public function getSetting($name, $default = null)
    {
        return isset($this->settings[$name]) ? $this->settings[$name] : $default;
    }

    /**
     * @param string $name Setting name to set
     * @param array|self $value Value
     * @return self
     */
    public function setSetting($name, $value)
    {
        $this->settings[$name] = $value instanceof self ? $value->toArray() : $value;
        return $this;
    }

    public function getSettings($name)
    {
        return new Settings(isset($this->settings[$name]) ? $this->settings[$name] : []);
    }

    public function each(callable $callback)
    {
        foreach ($this->settings as $key => $value) {
            $callback(new Settings($value), $key);
        }
    }


    public function toArray()
    {
        return $this->settings;
    }

    public function setSettings($settings)
    {
        if (is_array($settings)) {
            foreach ($settings as $name => $value) {
                $this->settings[$name] = $value;
            }
        } elseif ($settings instanceof self) {
            foreach ($settings->toArray() as $name => $value) {
                $this->settings[$name] = $value;
            }
        }
        return $this;
    }

    public function load($fileName)
    {
        if (file_exists($fileName)) {
            $this->settings = include_once $fileName;
            return true;
        } else
            return false;
    }
}