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
        $this->handleRequest();

        $this->renderResponse();

        return $this;
    }

    /**
     * @return mixed
     */
    public function handleRequest()
    {
        $dispatcher = $this->getDispatcher();
        $dispatcher->register();
        $dispatcher->parseRequest($this->getRequest());

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
            $handlerPath = $this->getDispatcher()->getHandler();

            $handlerPathParts = explode('::', $handlerPath);

            list($controllerClass, $action) = $handlerPathParts;

            /* @var ControllerInterface $controller */
            $controller = new $controllerClass();
            $this->setController($controller);
            $controller->setApplication($this);

            $handlerOutput = $controller->{$action}($this->getDispatcher()->getRouteParameters());
        } catch (\Exception $e) {
            // TODO: better error handling (i.e. new ErrorHandler($e))
            $handlerOutput = $e->getMessage();
        }

        return $handlerOutput;
    }
}
