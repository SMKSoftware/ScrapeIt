<?php
namespace SmkSoftware\ScrapeIt\StateStorage;

use SmkSoftware\ScrapeIt\Request;

class MemoryStateStorage extends StateStorage
{
    /** @var Request[] */
    private $urls = [];

    /** @var Request[] */
    private $completed = [];

    private $prepended = [];

    private function exists($key)
    {
        return isset($this->urls[$key]) || isset($this->completed[$key]) || isset($this->prepended[$key]);
    }

    public function add(Request $request, $checkExistence = true, $prepend = false)
    {
        $key = strval($request);
        if (!$checkExistence || !$this->exists($key)) {
            if ($prepend)
                $this->prepended = [$key => $request] + $this->prepended;
            else
                $this->urls[$key] = $request;
        }
    }

    public function complete(Request $request)
    {
        $this->completed[strval($request)] = $request;
    }

    public function next()
    {
        foreach ($this->urls as $key => $value) {
            if ($this->prepended) {
                foreach ($this->prepended as $prepKey => $prepValue)
                    yield $key => $value;
            }
            yield $key => $value;
        }
    }
}