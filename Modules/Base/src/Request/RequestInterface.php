<?php

namespace Framework\Base\Request;

/**
 * Interface RequestInterface
 * @package Framework\Base\Request
 */
interface RequestInterface
{
    /**
     * @param array $serverInformationMap
     * @return mixed
     */
    public function setServer(array $serverInformationMap = []);

    /**
     * @return mixed
     */
    public function getUri();

    /**
     * @return string
     */
    public function getMethod();
}
