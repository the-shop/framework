<?php

namespace Framework\Application\RestApi;

use Framework\Base\Application\BaseApplication;
use Framework\Base\Application\ControllerInterface;
use Framework\Base\Render\Json;
use Framework\Http\Response\Response;

/**
 * Class RestApi
 * @package Framework\Application\RestApi
 */
class RestApi extends BaseApplication
{
    /**
     * @return $this
     */
    public function run()
    {
        $this->setExceptionHandler(new ExceptionHandler());

        $this->handleRequest();

        $this->renderResponse();

        return $this;
    }

    /**
     * @return mixed
     */
    public function handleRequest()
    {
        $handlerOutput = $this->handle();

        $this->buildResponse($handlerOutput);

        return $handlerOutput;
    }

    /**
     * Renders response
     */
    public function renderResponse()
    {
        $jsonRender = new Json();
        $jsonRender->render($this->getResponse());
    }

    /**
     * @param $handlerOutput
     * @return Response
     */
    public function buildResponse($handlerOutput)
    {
        $response = new Response();
        $response->setBody($handlerOutput);
        $this->setResponse($response);

        return $response;
    }

    /**
     * @return mixed
     */
    protected function handle()
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

        return $handlerOutput;
    }
}
