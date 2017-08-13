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
        $uri = $this->getRequest()->getUri();

        $router = $this->getRouter();

        $controller = $router->getUriHandler($uri);

        $this->setController($controller);

        if (!in_array($this->getRequest()->getMethod(), $controller->getRegisteredRequestMethods())) {
            throw new \RuntimeException('Not implemented');
        }

        $controller->setApplication($this);

        $this->handleRequest();

        $this->renderResponse();

        return $this;
    }

    public function handleRequest()
    {
        $handlerOutput = $this->handle($this->getController());

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
