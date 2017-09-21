<?php

namespace Framework\Base\Terminal\Router;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Request\RequestInterface;
use Framework\Base\Router\DispatcherInterface;
use Framework\Base\Terminal\Input\TerminalInput;

/**
 * Class Dispatcher
 * @package Framework\Base\Terminal\Router
 */
class Dispatcher implements DispatcherInterface
{
    use ApplicationAwareTrait;
    /**
     * @var array
     */
    private $routes = [];

    /**
     * @var array
     */
    private $routeParameters = [];

    /**
     * @var
     */
    private $handler = null;

    /**
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function register()
    {
    }

    public function getRouteParameters(): array
    {
        return $this->routeParameters;
    }

    /**
     * @param array $routesDefinition
     * @return $this
     */
    public function addRoutes(array $routesDefinition = [])
    {
        $this->routes = $routesDefinition;

        return $this;
    }

    /**
     * @param RequestInterface $request
     * @return $this
     */
    public function parseRequest(RequestInterface $request)
    {
        $inputHandler = new TerminalInput($request);
        $commandName = $inputHandler->getInputCommand();

        if (array_key_exists($commandName, $this->getRoutes()) === false) {
            throw new \InvalidArgumentException('Command name ' . $commandName . ' is not registered.', 404);
        }

        $inputParams = $inputHandler->getInputParameters();
        $this->routeParameters['command'] = $commandName;

        $this->setHandler($this->getRoutes()[$commandName]['handler']);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @param string|null $fullyQualifiedClassName
     * @return $this
     */
    public function setHandler(string $fullyQualifiedClassName = null)
    {
        if (($fullyQualifiedClassName !== null) === true) {
            $this->handler = $fullyQualifiedClassName;
        }

        return $this;
    }
}
