<?php

namespace Framework\Http\Router;

use Framework\Application\RestApi\MethodNotAllowedException;
use Framework\Application\RestApi\NotFoundException;
use Framework\Base\Application\ApplicationAwareInterface;
use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Request\RequestInterface;

/**
 * Class Dispatcher
 * @package Framework\Http\Router
 */
class Dispatcher implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    /**
     * @var array
     */
    private $routes = [];

    /**
     * @var \FastRoute\Dispatcher|null
     */
    private $dispatcher = null;

    /**
     * @var string|null
     */
    private $handler = null;

    /**
     * @var array
     */
    private $routeParameters = [];

    /**
     * @param array $routes
     * @return $this
     */
    public function addRoutes(array $routes = [])
    {
        $this->routes = array_merge($this->routes, $routes);

        return $this;
    }

    /**
     * @param RequestInterface $request
     * @return array
     * @throws MethodNotAllowedException
     * @throws \Exception
     */
    public function parseRequest(RequestInterface $request)
    {
        // Fetch method and URI from somewhere
        $httpMethod = $request->getMethod();
        $uri = $request->getUri();

        // Strip query string (?foo=bar) and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $routeInfo = $this->dispatcher
            ->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                throw new NotFoundException('Route not found.');
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                $exception = new MethodNotAllowedException('Request method not allowed');
                $exception->setAllowedMethods($allowedMethods);
                throw $exception;
                break;
            case \FastRoute\Dispatcher::FOUND:
                $this->handler = $routeInfo[1];
                $this->routeParameters = $routeInfo[2];

                return $routeInfo;
                break;

            default:
                throw new \Exception('Router exception.');
        }
    }

    /**
     * @return \FastRoute\Dispatcher
     */
    public function register()
    {
        $routes = $this->routes;
        $callback = function(\FastRoute\RouteCollector $routeCollector) use ($routes) {
            foreach ($this->routes as $route) {
                list ($method, $path, $handler) = $route;
                $routeCollector->addRoute(strtoupper($method), $path, $handler);
            }
        };

        $this->dispatcher = \FastRoute\simpleDispatcher($callback);

        return $this->dispatcher;
    }

    /**
     * @return null|string
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @return array
     */
    public function getRouteParameters()
    {
        return $this->routeParameters;
    }
}
