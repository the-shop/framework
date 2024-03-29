<?php

namespace Framework\Base\Application;

use Framework\Base\Application\Exception\ExceptionHandler;
use Framework\Base\Application\Exception\GuzzleHttpException;
use Framework\Base\Application\Exception\MethodNotAllowedException;
use Framework\Base\Auth\RequestAuthorization;
use Framework\Base\Event\ListenerInterface;
use Framework\Base\Logger\LoggerInterface;
use Framework\Base\Logger\LogInterface;
use Framework\Base\Logger\MemoryLogger;
use Framework\Base\Manager\RepositoryManager;
use Framework\Base\Manager\RepositoryManagerInterface;
use Framework\Base\Render\RenderInterface;
use Framework\Base\Request\RequestInterface;
use Framework\Base\Response\ResponseInterface;
use Framework\Base\Router\DispatcherInterface;
use Framework\Http\Response\Response;
use Framework\Terminal\Commands\Cron\CronJobInterface;
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
    const EVENT_APPLICATION_PARSE_REQUEST_PRE = 'EVENT\APPLICATION\PARSE_REQUEST_PRE';

    /**
     * @const string
     */
    const EVENT_APPLICATION_PARSE_REQUEST_POST = 'EVENT\APPLICATION\PARSE_REQUEST_POST';

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
     * @var RepositoryManagerInterface|null
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
     * @var LoggerInterface[]
     */
    private $loggers = [];

    /**
     * @var array
     */
    private $aclRules = [];

    /**
     * @var ServicesRegistry|null
     */
    private $servicesRegistry = null;

    /**
     * @var ApplicationConfiguration
     */
    private $configuration = null;

    /**
     * @var \Framework\Base\Auth\RequestAuthorization
     */
    private $requestAuthorization = null;

    /**
     * @var null|string
     */
    private $rootPath = null;

    /**
     * @var array
     */
    protected $registeredCronJobs = [];

    /**
     * Has to build instance of RequestInterface, set it to BaseApplication and return it
     *
     * @return RequestInterface
     */
    abstract public function buildRequest();

    /**
     * BaseApplication constructor.
     * @param ApplicationConfiguration|null $applicationConfiguration
     */
    public function __construct(ApplicationConfiguration $applicationConfiguration = null)
    {
        if ($applicationConfiguration === null) {
            $applicationConfiguration = new ApplicationConfiguration();
        }

        $this->configuration = $applicationConfiguration;
        /**
         * @todo move root path definition to ConfigService once its implemented
         */
        $this->setRootPath(
            realpath(
                dirname(__DIR__, 4)
            )
        );

        $this->setExceptionHandler(new ExceptionHandler());
        $this->bootstrap();
    }

    /**
     * @param string $serviceClass
     * @return ServiceInterface
     */
    public function getService(string $serviceClass)
    {
        return $this->servicesRegistry->get($serviceClass);
    }

    /**
     * @param \Framework\Base\Application\ServiceInterface $service
     * @param bool                                         $overwriteExisting
     *
     * @return \Framework\Base\Application\ApplicationInterface
     */
    public function registerService(ServiceInterface $service, bool $overwriteExisting = false): ApplicationInterface
    {
        $this->servicesRegistry->registerService($service, $overwriteExisting);

        return $this;
    }

    /**
     * @return ApplicationConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
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

            $this->triggerEvent(self::EVENT_APPLICATION_PARSE_REQUEST_PRE);
            $this->parseRequest($request);
            $this->triggerEvent(self::EVENT_APPLICATION_PARSE_REQUEST_POST);

            $this->triggerEvent(self::EVENT_APPLICATION_HANDLE_REQUEST_PRE);
            $response = $this->handle();
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
     * @return $this
     */
    public function parseRequest(RequestInterface $request)
    {
        $dispatcher = $this->getDispatcher();
        $dispatcher->setApplication($this);
        $dispatcher->register();
        $dispatcher->parseRequest($request);

        return $this;
    }

    /**
     * @return ResponseInterface
     */
    public function handle()
    {
        $dispatcher = $this->getDispatcher();

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
     * @return Bootstrap
     */
    public function bootstrap()
    {
        $this->servicesRegistry = new ServicesRegistry();
        $this->servicesRegistry->setApplication($this);

        $bootstrap = new Bootstrap();
        $registerModules = $this->getConfiguration()->getRegisteredModules();
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
                    throw new \RuntimeException('Listeners "' . $listenerClass . '" must implement ListenerInterface.');
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
     * @return $this
     */
    public function removeAllEventListeners()
    {
        $this->events = [];

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
     * @param \Framework\Base\Manager\RepositoryManagerInterface $repositoryManager
     * @return $this
     */
    public function setRepositoryManager(RepositoryManagerInterface $repositoryManager)
    {
        $this->repositoryManager = $repositoryManager;

        return $this;
    }

    /**
     * @return \Framework\Base\Manager\RepositoryManagerInterface|null
     */
    public function getRepositoryManager()
    {
        if ($this->repositoryManager === null) {
            $repository = new RepositoryManager();
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
     *
     * @return \Framework\Base\Application\ApplicationInterface
     * @throws \RuntimeException
     */
    public function log(LogInterface $log): ApplicationInterface
    {
        if (count($this->getLoggers()) === 0) {
            $this->addLogger(new MemoryLogger());
        }
        foreach ($this->getLoggers() as $logger) {
            $logger->log($log);
        }
        return $this;
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return \Framework\Base\Application\ApplicationInterface
     */
    public function addLogger(LoggerInterface $logger): ApplicationInterface
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
     * @param string    $method
     * @param string    $uri
     * @param array     $params
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws GuzzleHttpException
     * @throws MethodNotAllowedException
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
            $guzzleHttpResponse = $client->request($method, $uri, $params);
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

        $response = new Response();
        $response->setCode($guzzleHttpResponse->getStatusCode());
        $response->setBody($guzzleHttpResponse->getBody());

        return $response;
    }

    /**
     * @param array $aclConfig
     * @return $this
     */
    public function setAclRules(array $aclConfig = [])
    {
        $this->aclRules = $aclConfig;

        return $this;
    }

    /**
     * @return array
     */
    public function getAclRules()
    {
        return $this->aclRules;
    }

    /**
     * @param string $path
     *
     * @return \Framework\Base\Application\ApplicationInterface
     */
    public function setRootPath(string $path): ApplicationInterface
    {
        $this->rootPath = $path;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRootPath()
    {
        return $this->rootPath;
    }

    /**
     * @param \Framework\Base\Auth\RequestAuthorization $requestAuthorization
     *
     * @return \Framework\Base\Application\ApplicationInterface
     */
    public function setRequestAuthorization(RequestAuthorization $requestAuthorization): ApplicationInterface
    {
        $this->requestAuthorization = $requestAuthorization;

        return $this;
    }

    /**
     * @return RequestAuthorization|null
     */
    public function getRequestAuthorization()
    {
        return $this->requestAuthorization;
    }

    /**
     * @return array
     */
    public function getRegisteredCronJobs(): array
    {
        return $this->registeredCronJobs;
    }

    /**
     * @param \Framework\Terminal\Commands\Cron\CronJobInterface $cronJob
     *
     * @return \Framework\Base\Application\ApplicationInterface
     */
    public function registerCronJob(CronJobInterface $cronJob): ApplicationInterface
    {
        $this->registeredCronJobs[] = $cronJob;

        return $this;
    }
}
