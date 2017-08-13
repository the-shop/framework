<?php

namespace Framework\Base\Router;

/**
 * Interface RouterInterface
 * @package Framework\Base\Router
 */
interface RouterInterface
{
    /**
     * @param $uri
     * @return mixed
     */
    public function parse($uri);

    /**
     * @param $uri
     * @return mixed
     */
    public function getUriHandler();
}
