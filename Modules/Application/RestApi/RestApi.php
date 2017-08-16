<?php

namespace Framework\Application\RestApi;

use Framework\Base\Application\BaseApplication;
use Framework\Base\Application\ControllerInterface;
use Framework\Base\Render\Json;
use Framework\Base\Request\RequestInterface;
use Framework\Http\Request\Request;
use Framework\Http\Response\Response;
use Framework\Http\Response\ResponseInterface;

/**
 * Class RestApi
 * @package Framework\Application\RestApi
 */
class RestApi extends BaseApplication
{
    /**
     * RestApi constructor.
     * @param array $registerModules
     */
    public function __construct(array $registerModules = [])
    {
        parent::__construct($registerModules);

        $this->setExceptionHandler(new ExceptionHandler());
        $this->setRenderer(new Json());
    }

    /**
     * @return RequestInterface
     */
    public function buildRequest()
    {
        $request = $this->getResolver()
            ->resolve(Request::class);

        $request->setPost(isset($_POST) ? $_POST : []);
        $request->setQuery(isset($_GET) ? $_GET : []);
        $request->setFiles(isset($_FILES) ? $_FILES : []);
        $request->setServer($_SERVER);

        unset($_POST);
        unset($_GET);
        unset($_FILES);

        $this->setRequest($request);

        return $request;
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

        $response = new Response();
        $response->setBody($handlerOutput);
        $this->setResponse($response);

        return $response;
    }
}
