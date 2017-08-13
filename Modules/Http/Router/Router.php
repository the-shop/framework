<?php

namespace Framework\Http\Router;

use Framework\Application\RestApi\NotFoundException;
use Framework\Base\Application\ApplicationAwareInterface;
use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Application\ControllerInterface;

/**
 * Class Router
 * @package Framework\Http\Router
 */
class Router implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;

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
    public function getUriHandler($uri)
    {
        if (!isset($this->registry[$uri])) {
            throw new NotFoundException("Route for URI " . $uri ." is not registered");
        }

        $resolver = $this->getApplication()
            ->getResolver();

        return $resolver->resolve($this->registry[$uri]);
    }
}
