<?php

namespace Framework\Base\Application;

use Framework\Base\Di\Resolver;
use Framework\Base\Events\ListenerInterface;

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
     * @return \Framework\Http\Router\Dispatcher
     */
    public function getDispatcher();

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
     * @return Resolver
     */
    public function getResolver();

    /**
     * @param string $eventName
     * @return mixed
     */
    public function triggerEvent(string $eventName);

    /**
     * @param string $eventName
     * @param ListenerInterface $listener
     * @return mixed
     */
    public function listen(string $eventName, ListenerInterface $listener);
}
