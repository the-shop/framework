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
     * @return \Framework\Http\Response\Response
     */
    public function run()
    {
        $this->handleRequest();

        $this->renderResponse();

        return $this->getResponse();
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
     * @return \Framework\Base\Application\ControllerInterface
     */
    protected function resolveHandler()
    {
        $routeHandler = $this->getRouter()
            ->parse($_SERVER['REQUEST_URI']);

        if (!in_array($this->getRequest()->getMethod(), $routeHandler->getRegisteredRequestMethods())) {
            // TODO: implement custom exception for this
            throw new \RuntimeException('Not implemented');
        }

        $routeHandler->setApplication($this);

        return $routeHandler;
    }

    /**
     * @return mixed|string
     */
    protected function handleRequest()
    {
        $routeHandler = $this->resolveHandler();

        $handlerOutput = $this->handle($routeHandler);

        $this->buildResponse($handlerOutput);

        return $handlerOutput;
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
     * @param ControllerInterface $routeHandler
     * @return mixed|string
     */
    protected function handle(ControllerInterface $routeHandler)
    {
        try {
            $routeHandler->setApplication($this);
            $handlerOutput = $routeHandler->handle();
        } catch (\Exception $e) {
            $handlerOutput = $e->getMessage();
        }

        return $handlerOutput;
    }
}
