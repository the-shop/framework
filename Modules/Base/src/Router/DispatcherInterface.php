<?php

namespace Framework\Base\Router;

use Framework\Base\Application\ApplicationAwareInterface;
use Framework\Base\Application\ControllerInterface;
use Framework\Base\Request\RequestInterface;

interface DispatcherInterface extends ApplicationAwareInterface
{
    /**
     * @return mixed
     */
    public function register();

    /**
     * @param RequestInterface $request
     * @return mixed
     */
    public function parseRequest(RequestInterface $request);

    /**
     * @return ControllerInterface
     */
    public function getHandler();

    /**
     * @return array
     */
    public function getRoutes();

    /**
     * @return mixed
     */
    public function getRouteParameters();

    /**
     * @param array $routesDefinition
     * @return mixed
     */
    public function addRoutes(array $routesDefinition = []);
}
