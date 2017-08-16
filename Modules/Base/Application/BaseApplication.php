<?php

namespace Framework\Base\Application;

use Framework\Application\RestApi\ExceptionHandler;
use Framework\Base\Di\Resolver;
use Framework\Base\Events\ListenerInterface;
use Framework\Base\Manager\Repository;
use Framework\Base\Manager\RepositoryInterface;
use Framework\Base\Render\RenderInterface;
use Framework\Base\Request\RequestInterface;
use Framework\Http\Request\Request;
use Framework\Http\Response\Response;
use Framework\Http\Router\Dispatcher;

/**
 * Class BaseApplication
 * @package Framework\Base\Application
 */
abstract class BaseApplication implements ApplicationInterface
{
    /**
     * @var Dispatcher|null
     */
    private $dispatcher = null;

    /**
     * @var Request|null
     */
    private $request = null;

    /**
     * @var Response|null
     */
    private $response = null;

    /**
     * @var ControllerInterface|null
     */
    private $controller = null;

    /**
     * @var RepositoryInterface|null
     */
    private $repositoryManager = null;

    /**
     * @var Resolver|null
     */
    private $resolver = null;

    /**
     * @var ExceptionHandler|null
     */
    private $exceptionHandler = null;

    /**
     * @var array
     */
    private $events = [];

    /**
     * @var RenderInterface|null
     */
    private $renderer = null;

    /**
     * @var array
     */
    private $registeredModules = [];

    /**
     * @return $this
     */
    abstract public function handle(RequestInterface $request);

    /**
     * @return RequestInterface
     */
    abstract public function buildRequest();

    /**
     * BaseApplication constructor.
     * @param array $registerModules
     */
    public function __construct(array $registerModules = [])
    {
        $this->registerModules($registerModules);
        $this->bootstrap();
    }

    /**
     * @param array $moduleClassList
     * @return $this
     */
    public function registerModules(array $moduleClassList = [])
    {
        $this->registeredModules = array_merge($this->registeredModules, $moduleClassList);

        $this->registeredModules = array_unique($this->registeredModules);

        return $this;
    }

    /**
     * @return array
     */
    public function getRegisteredModules()
    {
        return $this->registeredModules;
    }

    /**
     * @return Bootstrap
     */
    public function bootstrap()
    {
        $bootstrap = new Bootstrap();
        $registerModules = $this->getRegisteredModules();
        $bootstrap->registerModules($registerModules, $this);

        return $bootstrap;
    }

    public function triggerEvent(string $eventName)
    {
        if (array_key_exists($eventName, $this->events) === true) {
            foreach ($this->events[$eventName] as $listener) {
                /* @var ListenerInterface $listener */
                $listener->handle();
            }
        }
    }

    public function listen(string $eventName, ListenerInterface $listener)
    {
        if (array_key_exists($eventName, $this->events) === false) {
            $this->events[$eventName] = [];
        }

        $this->events[$eventName][] = $listener;

        return $this;
    }

    /**
     * @param ExceptionHandler $exceptionHandler
     * @return $this
     */
    public function setExceptionHandler(ExceptionHandler $exceptionHandler)
    {
        $this->exceptionHandler = $exceptionHandler;

        return $this;
    }

    /**
     * @return ExceptionHandler|null
     */
    public function getExceptionHandler()
    {
        return $this->exceptionHandler;
    }

    /**
     * @param RenderInterface $render
     * @return $this
     */
    public function setRenderer(RenderInterface $render)
    {
        $this->renderer = $render;

        return $this;
    }

    /**
     * @return RenderInterface
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * @param \Framework\Base\Manager\RepositoryInterface $repositoryManager
     * @return $this
     */
    public function setRepositoryManager(RepositoryInterface $repositoryManager)
    {
        $this->repositoryManager = $repositoryManager;

        return $this;
    }

    /**
     * @return \Framework\Base\Manager\RepositoryInterface|null
     */
    public function getRepositoryManager()
    {
        if ($this->repositoryManager === null) {
            $repositoryManager = $this->getResolver()
                ->resolve(Repository::class);
            $this->setRepositoryManager($repositoryManager);
        }

        return $this->repositoryManager;
    }

    /**
     * @return \Framework\Http\Router\Dispatcher
     */
    public function getDispatcher()
    {
        if ($this->dispatcher === null) {
            // TODO: support non HTTP routes
            $this->dispatcher = new Dispatcher();
        }

        return $this->dispatcher;
    }

    /**
     * @param Dispatcher $dispatcher
     * @return $this
     */
    public function setDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * @return Request|null
     */
    public function getRequest()
    {
        if ($this->request === null) {
            throw new \RuntimeException('Request object not set.');
        }

        return $this->request;
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request) {
        $this->request = $request;

        return $this;
    }

    /**
     * @param Response $response
     * @return $this
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return \Framework\Http\Response\Response
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * @param ControllerInterface $controller
     * @return $this
     */
    public function setController(ControllerInterface $controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * @return ControllerInterface|null
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return Resolver
     */
    public function getResolver()
    {
        if ($this->resolver === null) {
            $this->resolver = new Resolver();
        }
        return $this->resolver;
    }
}
