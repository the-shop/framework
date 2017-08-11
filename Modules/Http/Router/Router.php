<?php

namespace Framework\Http\Router;

use Framework\Application\RestApi\NotFoundException;
use Framework\Base\Module\BaseModule;

class Router extends BaseModule
{
    private $registry = [];

    public function bootstrap()
    {
    }

    public function registerRoutes($routes)
    {
        $this->registry = array_merge($this->registry, $routes);

        return $this;
    }

    /**
     * @param $uri
     * @return \Framework\Http\Request\ApiMethodInterface
     */
    public function parse($uri)
    {
        if (!isset($this->registry[$uri])) {
            throw new NotFoundException("Route for URI " . $uri ." is not registered");
        }

        return new $this->registry[$uri];
    }
}