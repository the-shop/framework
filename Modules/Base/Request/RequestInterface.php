<?php

namespace Framework\Base\Request;

/**
 * Interface RequestInterface
 * @package Framework\Base\Request
 */
interface RequestInterface
{
    /**
     * @param $uri
     * @return mixed
     */
    public function setUri($uri);

    /**
     * @return mixed
     */
    public function getUri();

    /**
     * @return string
     */
    public function getMethod();
}
