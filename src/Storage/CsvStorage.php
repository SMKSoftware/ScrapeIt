<?php
namespace SmkSoftware\ScrapeIt\Storage;


use SmkSoftware\ScrapeIt\ResultItem;
use SmkSoftware\ScrapeIt\Settings;

class CsvStorage extends Storage
{
    private $handle;

    private $fileName;
    private $firstLine = true;

    const DELIMITER = 'delimiter';
    const LINE_BREAKS = 'lineBreaks';
    const ADD_HEADER = 'headers';
    const HEADER_NAMES = 'names';

    private $defaultSettings = [
        self::DELIMITER => ',',
        self::LINE_BREAKS => "\n",
        self::ADD_HEADER => true
    ];

    private $delimiter;
    private $lineBreaks;
    private $addHeader;


    /**
     * CsvStorage constructor.
     * @param string|null $fileName Output filename is not null, stdin otherwise
     * @param Settings|array|null $settings
     * @param null $name
     */
    public function __construct($fileName = null, $settings = null, $name = null)
    {
        $this->format = "csv";
        $this->fileName = $fileName;
        $this->name = $name;

        $this->settings = new Settings($this->defaultSettings);
        $this->settings->setSettings($settings);

        $this->delimiter = $this->settings->getSetting(self::DELIMITER);
        $this->lineBreaks = $this->settings->getSetting(self::LINE_BREAKS);
        $this->addHeader = $this->settings->getSetting(self::ADD_HEADER);
    }

    public function add(ResultItem $item)
    {
        $result = '';
        $header = '';
        $fieldNames = null;
        foreach ($item as $key => $value) {
            if ($this->firstLine) {
                if ($header)
                    $header .= $this->delimiter;
                else
                    $fieldNames = $item->getFieldNames();
                $header .= $this->escapeValue(isset($fieldNames[$key]) ? $fieldNames[$key] : $key);
            }
            if ($result) $result .= $this->delimiter;
            $result .= $this->escapeValue($value);
        }

        if ($result) {
            $this->firstLine = false;

            if ($header)
                fwrite($this->handle, $header . $this->lineBreaks);

            fwrite($this->handle, $result . $this->lineBreaks);
        }
    }

    public function escapeValue($value)
    {
        if (strpos($value, $this->delimiter) !== false) {
            $value = '"' . str_replace('"', '""', $value) . '"';
        }
        return $value;
    }

    public function init()
    {
        $this->handle = fopen($this->fileName, 'w');
        if (!$this->handle) $this->logger()->error("Can't open storage file", ['file' => $this->fileName, 'error' => error_get_last()]);
    }

    public function destroy()
    {
        if ($this->handle) fclose($this->handle);
    }
}