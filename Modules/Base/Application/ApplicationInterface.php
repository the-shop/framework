<?php

namespace Framework\Base\Application;

use Framework\Base\Di\Resolver;

/**
 * Interface ApplicationInterface
 * @package Framework\Base\Application
 */
interface ApplicationInterface
{
    /**
     * @return mixed
     */
    public function run();

    /**
     * @return \Framework\Base\Manager\RepositoryInterface|null
     */
    public function getRepositoryManager();

    /**
     * @return \Framework\Http\Request\Request
     */
    public function getRequest();

    /**
     * @return \Framework\Http\Router\Router
     */
    public function getRouter();

    /**
     * @param ControllerInterface $controller
     * @return ApplicationInterface
     */
    public function setController(ControllerInterface $controller);

    /**
     * @return ControllerInterface|null
     */
    public function getController();

    /**
     * @param string $className
     * @return mixed
     */
    public function setRouterClass(string $className);

    /**
     * @return Resolver
     */
    public function getResolver();
}
