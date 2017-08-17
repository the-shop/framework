<?php

namespace Framework\Base\Application;

use Framework\Base\Di\Resolver;
use Framework\Base\Request\RequestInterface;
use Framework\Base\Response\ResponseInterface;

/**
 * Interface ApplicationInterface
 * @package Framework\Base\Application
 */
interface ApplicationInterface
{
    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function handle(RequestInterface $request);

    /**
     * @return \Framework\Base\Manager\RepositoryInterface|null
     */
    public function getRepositoryManager();

    /**
     * @return ResponseInterface
     */
    public function getRequest();

    /**
     * @param ResponseInterface $response
     * @return mixed
     */
    public function setResponse(ResponseInterface $response);

    /**
     * @return ResponseInterface
     */
    public function getResponse();

    /**
     * @return \Framework\Base\Router\DispatcherInterface
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
     * @param mixed $payload
     * @return mixed
     */
    public function triggerEvent(string $eventName, $payload = null);

    /**
     * @param string $eventName
     * @param string $listenerClass
     * @return mixed
     */
    public function listen(string $eventName, string $listenerClass);

    /**
     * @param array $moduleClassList
     * @return mixed
     */
    public function registerModules(array $moduleClassList = []);
}
