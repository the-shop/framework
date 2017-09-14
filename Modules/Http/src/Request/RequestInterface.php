<?php

namespace Framework\Http\Request;

/**
 * Interface RequestInterface
 * @package Framework\Http\Request
 */
interface RequestInterface extends \Framework\Base\Request\RequestInterface
{
    /**
     * @param string $requestMethod
     * @return mixed
     */
    public function setMethod(string $requestMethod);

    /**
     * @param string $uri
     * @return mixed
     */
    public function setUri(string $uri);
}
