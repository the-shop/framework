<?php

namespace Framework\Base\Application;

use Framework\Base\Application\Exception\ExceptionHandler;
use Framework\Base\Application\Exception\GuzzleHttpException;
use Framework\Base\Application\Exception\MethodNotAllowedException;
use Framework\Base\Event\ListenerInterface;
use Framework\Base\Logger\LoggerInterface;
use Framework\Base\Logger\LogInterface;
use Framework\Base\Logger\DummyLogger;
use Framework\Base\Manager\Repository;
use Framework\Base\Manager\RepositoryInterface;
use Framework\Base\Render\RenderInterface;
use Framework\Base\Request\RequestInterface;
use Framework\Base\Response\ResponseInterface;
use Framework\Base\Router\DispatcherInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Class BaseApplication
 * @package Framework\Base\Application
 */
abstract class BaseApplication implements ApplicationInterface, ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    /**
     * @const string
     */
    const EVENT_APPLICATION_BUILD_REQUEST_PRE = 'EVENT\APPLICATION\BUILD_REQUEST_PRE';

    /**
     * @const string
     */
    const EVENT_APPLICATION_BUILD_REQUEST_POST = 'EVENT\APPLICATION\BUILD_REQUEST_POST';

    /**
     * @const string
     */
    const EVENT_APPLICATION_HANDLE_REQUEST_PRE = 'EVENT\APPLICATION\HANDLE_REQUEST_PRE';

    /**
     * @const string
     */
    const EVENT_APPLICATION_HANDLE_REQUEST_POST = 'EVENT\APPLICATION\HANDLE_REQUEST_POST';

    /**
     * @const string
     */
    const EVENT_APPLICATION_RENDER_RESPONSE_PRE = 'EVENT\APPLICATION\RENDER_REQUEST_PRE';

    /**
     * @const string
     */
    const EVENT_APPLICATION_RENDER_RESPONSE_POST = 'EVENT\APPLICATION\RENDER_REQUEST_POST';

    /**
     * @var DispatcherInterface|null
     */
    private $dispatcher = null;

    /**
     * @var RequestInterface|null
     */
    private $request = null;

    /**
     * @var ResponseInterface|null
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
     * @var LoggerInterface[]
     */
    private $loggers = [];

    /**
     * Has to build instance of RequestInterface, set it to BaseApplication and return it
     *
     * @return RequestInterface
     */
    abstract public function buildRequest();

    /**
     * BaseApplication constructor.
     * @param array $registerModules
     */
    public function __construct(array $registerModules = [])
    {
        $this->setExceptionHandler(new ExceptionHandler());
        $this->registerModules($registerModules);
        $this->bootstrap();
    }

    /**
     * Main application entry point
     *
     * @return $this
     */
    public function run()
    {
        try {
            $this->triggerEvent(self::EVENT_APPLICATION_BUILD_REQUEST_PRE);
            $request = $this->getRequest();
            $this->triggerEvent(self::EVENT_APPLICATION_BUILD_REQUEST_POST);

            $this->triggerEvent(self::EVENT_APPLICATION_HANDLE_REQUEST_PRE);
            $response = $this->handle($request);
            $this->triggerEvent(self::EVENT_APPLICATION_HANDLE_REQUEST_POST);

            $this->triggerEvent(self::EVENT_APPLICATION_RENDER_RESPONSE_PRE);
            $this->getRenderer()
                ->render($response);
            $this->triggerEvent(self::EVENT_APPLICATION_RENDER_RESPONSE_POST);
        } catch (\Exception $e) {
            $this->getExceptionHandler()
                ->handle($e);

            $this->getRenderer()
                ->render($this->getResponse());
        }

        return $this;
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function handle(RequestInterface $request)
    {
        $dispatcher = $this->getDispatcher();
        $dispatcher->register();
        $dispatcher->parseRequest($this->getRequest());

        $handlerPath = $dispatcher->getHandler();

        $handlerPathParts = explode('::', $handlerPath);

        list($controllerClass, $action) = $handlerPathParts;

        /* @var ControllerInterface $controller */
        $controller = new $controllerClass();
        $this->setController($controller);
        $controller->setApplication($this);

        $parameterValues = array_values($this->getDispatcher()->getRouteParameters());

        $handlerOutput = $controller->{$action}(...$parameterValues);

        $response = $this->getResponse();
        $response->setBody($handlerOutput);

        return $response;
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

    /**
     * @param string $eventName
     * @param null $payload
     * @return array
     */
    public function triggerEvent(string $eventName, $payload = null)
    {
        $responses = [];
        if (array_key_exists($eventName, $this->events) === true) {
            foreach ($this->events[$eventName] as $listenerClass) {
                $listener = new $listenerClass();
                if (!($listener instanceof ListenerInterface)) {
                    throw new \RuntimeException('Listener "' . $listenerClass . '" must implement ListenerInterface.');
                }
                $listener->setApplication($this);
                $responses[] = $listener->handle($payload);
            }
        }
        return $responses;
    }

    /**
     * @param string $eventName
     * @param string $listenerClass
     * @return $this
     */
    public function listen(string $eventName, string $listenerClass)
    {
        if (array_key_exists($eventName, $this->events) === false) {
            $this->events[$eventName] = [];
        }

        $this->events[$eventName][] = $listenerClass;

        return $this;
    }

    /**
     * @param string $eventName
     * @return $this
     */
    public function removeEventListeners(string $eventName)
    {
        if (isset($this->events[$eventName]) === true) {
            unset($this->events[$eventName]);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param ExceptionHandler $exceptionHandler
     * @return $this
     */
    public function setExceptionHandler(ExceptionHandler $exceptionHandler)
    {
        $this->exceptionHandler = $exceptionHandler;

        $this->exceptionHandler->setApplication($this);

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
            $repository = new Repository();
            $repository->setApplication($this);
            $this->setRepositoryManager($repository);
        }

        return $this->repositoryManager;
    }

    /**
     * @return \Framework\Base\Router\DispatcherInterface
     */
    public function getDispatcher()
    {
        if ($this->dispatcher === null) {
            throw new \RuntimeException('Dispatcher object not set.');
        }
        return $this->dispatcher;
    }

    /**
     * @param DispatcherInterface $dispatcher
     * @return $this
     */
    public function setDispatcher(DispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        if ($this->request === null) {
            $this->buildRequest();
        }
        return $this->request;
    }

    /**
     * @param RequestInterface $request
     * @return $this
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @param ResponseInterface $response
     * @return $this
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
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
     * @param LogInterface $log
     * @return $this
     * @throws \RuntimeException
     */
    public function log(LogInterface $log)
    {
        if (count($this->getLoggers()) === 0) {
            $this->addLogger(new DummyLogger());
        }
        foreach ($this->getLoggers() as $logger) {
            $logger->log($log);
        }
        return $this;
    }

    /**
     * @param LoggerInterface $logger
     * @return $this
     */
    public function addLogger(LoggerInterface $logger)
    {
        $this->loggers[] = $logger;

        return $this;
    }

    /**
     * @return LoggerInterface[]
     */
    public function getLoggers()
    {
        return $this->loggers;
    }

    /**
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \Framework\Base\Application\Exception\GuzzleHttpException
     * @throws \Framework\Base\Application\Exception\MethodNotAllowedException
     */
    public function httpRequest(string $method, string $uri = '', array $params = [])
    {
        $allowedHttpMethods = [
            'GET',
            'POST',
            'PUT',
            'DELETE',
            'PATCH',
            'HEAD',
            'OPTIONS'
        ];

        if (is_string($method) === false ||
            in_array(strtoupper($method), $allowedHttpMethods, true) === false
        ) {
            $exception = new MethodNotAllowedException('Http method not allowed');
            $exception->setAllowedMethods($allowedHttpMethods);
            throw $exception;
        }

        $client = new Client();

        try {
            $response = $client->request($method, $uri, $params);
        } catch (RequestException $requestException) {
            if ($requestException->hasResponse() === false) {
                $message = $requestException->getMessage();
                $code = null;
            } else {
                $message = $requestException->getResponse()->getReasonPhrase();
                $code = $requestException->getResponse()->getStatusCode();
            }
            throw new GuzzleHttpException($message, $code);
        }

        return $response;
    }
}
