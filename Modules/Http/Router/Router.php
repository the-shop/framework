<?php

namespace Framework\Http\Router;

use Framework\Application\RestApi\NotFoundException;
use Framework\Base\Application\ControllerInterface;

/**
 * Class Router
 * @package Framework\Http\Router
 */
class Router
{
    /**
     * @var array
     */
    private $registry = [];

    /**
     * @param $routes
     * @return $this
     */
    public function registerRoutes($routes)
    {
        $this->registry = array_merge($this->registry, $routes);

        return $this;
    }

    /**
     * @param $uri
     * @return ControllerInterface
     */
    public function parse($uri)
    {
        if (!isset($this->registry[$uri])) {
            throw new NotFoundException("Route for URI " . $uri ." is not registered");
        }

        return new $this->registry[$uri];
    }
}
