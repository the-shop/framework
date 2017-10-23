<?php

namespace Framework\Terminal\Router;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Request\RequestInterface;
use Framework\Base\Router\DispatcherInterface;
use Framework\Terminal\Input\TerminalInput;

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
    private $commands = [];

    /**
     * @var array
     */
    private $commandParameters = [];

    /**
     * @var
     */
    private $handler = null;

    /**
     * @return array
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * @return array
     */
    public function getCommandParameters(): array
    {
        return $this->commandParameters;
    }

    /**
     * @param array $commands
     *
     * @return \Framework\Base\Router\DispatcherInterface
     */
    public function addCommands(array $commands): DispatcherInterface
    {
        $this->commands = array_merge($this->commands, $commands);

        return $this;
    }

    /**
     * @param RequestInterface $request
     * @return $this
     */
    public function parseRequest(RequestInterface $request)
    {
        // Parse input arguments
        $inputHandler = new TerminalInput($request);
        $commandName = $inputHandler->getInputCommand();

        // Let's check if command is registered
        if (array_key_exists($commandName, $this->getCommands()) === false) {
            throw new \InvalidArgumentException(
                'Command name ' . $commandName . ' is not registered.',
                404
            );
        }

        /* Let's grab route defined parameters, required and optional, cast them to lowercase
         so we can compare it with input arguments */
        $definedRequiredParams = array_map(
            'strtolower',
            $this->getCommands()[$commandName]['requiredParams']
        );
        $definedOptionalParams = array_map(
            'strtolower',
            $this->getCommands()[$commandName]['optionalParams']
        );

        // Let's grab input arguments
        $inputRequiredParams = $inputHandler->getInputParameters()['requiredParams'];
        $inputOptionalParams = $inputHandler->getInputParameters()['optionalParams'];

        $commandParameters = [];

        // Compare route defined required parameters with input required arguments
        foreach ($definedRequiredParams as $definedParam) {
            if (array_key_exists($definedParam, $inputRequiredParams) === false) {
                throw new \InvalidArgumentException('Invalid required arguments.', 403);
            }
            $commandParameters[$definedParam] = $inputRequiredParams[$definedParam];
        }

        // Compare route defined optional parameters with input optional arguments
        foreach ($definedOptionalParams as $definedOptionalParam) {
            if (array_key_exists($definedOptionalParam, $inputOptionalParams) === false) {
                throw new \InvalidArgumentException('Invalid optional arguments.', 403);
            }
            $commandParameters[$definedOptionalParam] = $inputOptionalParams[$definedOptionalParam];
        }

        // Set route parameters
        $this->commandParameters = $commandParameters;

        // Set route handler
        $this->setHandler($this->getCommands()[$commandName]['handler']);

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
        if (class_exists($fullyQualifiedClassName) === true) {
            $this->handler = $fullyQualifiedClassName;
        }

        return $this;
    }

    public function register()
    {
    }

    //Terminal translation

    /**
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->getCommands();
    }

    /**
     * @return array
     */
    public function getRouteParameters(): array
    {
        return $this->getCommandParameters();
    }

    /**
     * @param array $routesDefinition
     * @return $this
     */
    public function addRoutes(array $routesDefinition = []): DispatcherInterface
    {
        return $this->addCommands($routesDefinition);
    }
}
