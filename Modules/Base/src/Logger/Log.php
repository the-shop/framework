<?php

namespace Framework\Base\Logger;

class Log implements LogInterface
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var string|\Exception
     */
    private $payload;

    /**
     * @var bool
     */
    private $isException = false;

    public function __construct($payload)
    {
        if ($payload instanceof \Exception) {
            $this->isException = true;
        }
        $this->payload = $payload;
    }

    /**
     * @return \Exception|string
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getData(string $key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        return null;
    }

    /**
     * @return array
     */
    public function getAllData()
    {
        return $this->data;
    }

    /**
     * @param string $key
     * @param $value
     * @return $this
     */
    public function setData(string $key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function isException()
    {
        return $this->isException;
    }
}
