<?php

namespace Framework\Base\Terminal\Router;

use Framework\Base\Terminal\Input\TerminalInputInterface;

/**
 * Interface TerminalDispatcherInterface
 * @package Framework\Base\Terminal\Router
 */
interface TerminalDispatcherInterface
{
    /**
     * @param array $routesDefinition
     * @return mixed
     */
    public function addRoutes(array $routesDefinition = []);

    /**
     * @return mixed
     */
    public function getHandler();

    /**
     * @param TerminalInputInterface $terminalInput
     * @return mixed
     */
    public function parseRequest(TerminalInputInterface $terminalInput);
}
