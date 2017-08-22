<?php

namespace Framework\Base\Logger;

interface LogInterface
{
    /**
     * @param string $key
     * @param $value
     * @return mixed
     */
    public function setData(string $key, $value);

    /**
     * @param string $key
     * @return mixed
     */
    public function getData(string $key);

    public function getAllData();

    public function getPayload();

    public function isException();
}
