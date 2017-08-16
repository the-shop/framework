<?php

namespace Framework\Application\RestApi;

use Framework\Base\Application\BaseApplication;
use Framework\Base\Application\ControllerInterface;
use Framework\Base\Render\Json;
use Framework\Base\Request\RequestInterface;
use Framework\Http\Request\Request;
use Framework\Http\Response\Response;

/**
 * Class RestApi
 * @package Framework\Application\RestApi
 */
class RestApi extends BaseApplication
{
    /**
     * RestApi constructor.
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
        $request->setMethod($_SERVER['REQUEST_METHOD']);
        $request->setUri($_SERVER['REQUEST_URI']);

        unset($_POST);
        unset($_GET);
        unset($_FILES);

        $this->setRequest($request);

        return $request;
    }

    /**
     * @param RequestInterface $request
     * @return Response
     */
    public function handle(RequestInterface $request)
    {
        try {
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
        } catch (\Exception $e) {
            $handlerOutput = $this->getExceptionHandler()
                ->setApplication($this)
                ->handle($e);
        }

        $response = new Response();
        $response->setBody($handlerOutput);
        $this->setResponse($response);

        return $response;
    }
}
