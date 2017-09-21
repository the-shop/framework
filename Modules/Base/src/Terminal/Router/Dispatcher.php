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

        // Let's check if command is registered
        if (array_key_exists($commandName, $this->getRoutes()) === false) {
            throw new \InvalidArgumentException(
                'Command name ' . $commandName . ' is not registered.',
                404
            );
        }

        // Let's grab route defined parameters, required and optional, cast them to lowercase
        // so we can compare it with input arguments
        $definedRequiredParams = array_map(
            'strtolower',
            $this->getRoutes()[$commandName]['requiredParams']
        );
        $definedOptionalParams = array_map(
            'strtolower',
            $this->getRoutes()[$commandName]['optionalParams']
        );

        // Let's grab input arguments
        $inputRequiredParams = $inputHandler->getInputParameters()['requiredParams'];
        $inputOptionalParams = $inputHandler->getInputParameters()['optionalParams'];

        $routeParameters = [];

        // Compare route defined required parameters with input required arguments
        foreach ($definedRequiredParams as $definedParam) {
            if (array_key_exists($definedParam, $inputRequiredParams) === false) {
                throw new \InvalidArgumentException('Invalid required arguments.', 403);
            }
            $routeParameters[$definedParam] = $inputRequiredParams[$definedParam];
        }

        // Compare route defined required parameters with input required arguments
        foreach ($definedOptionalParams as $definedOptionalParam) {
            if (array_key_exists($definedOptionalParam, $inputOptionalParams) === false) {
                throw new \InvalidArgumentException('Invalid optional arguments.', 403);
            }
            $routeParameters[$definedOptionalParam] = $inputOptionalParams[$definedOptionalParam];
        }

        // Set route parameters
        $this->routeParameters = $routeParameters;

        // Set route handler
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
