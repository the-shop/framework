<?php

namespace Framework\Base\Terminal\Router;

use Framework\Base\Terminal\Input\TerminalInputInterface;

/**
 * Class Dispatcher
 * @package Framework\Base\Terminal\Router
 */
class Dispatcher implements TerminalDispatcherInterface
{
    /**
     * @var array
     */
    private $routes = [];

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
     * @param TerminalInputInterface $terminalInput
     * @return $this
     */
    public function parseRequest(TerminalInputInterface $terminalInput)
    {
        // TODO: implement
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
