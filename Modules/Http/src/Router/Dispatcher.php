<?php

namespace Framework\Http\Router;

use FastRoute\RouteCollector;
use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Application\Exception\MethodNotAllowedException;
use Framework\Base\Application\Exception\NotFoundException;
use Framework\Base\Request\RequestInterface;
use Framework\Base\Router\DispatcherInterface;

/**
 * Class Dispatcher
 * @package Framework\Http\Router
 */
class Dispatcher implements DispatcherInterface
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
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @param RequestInterface $request
     * @return array
     * @throws \Framework\Base\Application\Exception\MethodNotAllowedException
     * @throws \Exception
     */
    public function parseRequest(RequestInterface $request)
    {
        // Fetch method and URI from somewhere
        $httpMethod = $request->getMethod();
        $uri = $request->getUri();

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
        $routePrefix = $this->getApplication()
            ->getConfiguration()
            ->getPathValue('routePrefix');
        $callback = function (RouteCollector $routeCollector) use ($routes, $routePrefix) {
            $routeCollector->addGroup($routePrefix, function (RouteCollector $r) use ($routes) {
                foreach ($routes as $route) {
                    list ($method, $path, $handler) = $route;
                    $r->addRoute(strtoupper($method), $path, $handler);
                }
            });
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
