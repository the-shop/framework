<?php

namespace Framework\Application\RestApi;

use Framework\Base\Module\Module;
use Framework\Http\Request\Request;
use Framework\Http\Response\Response;
use Framework\Http\Router\Router;

class RestApi extends Module
{
    private $router = null;
    private $request = null;
    private $response = null;

    public function __construct()
    {
        $request = new \Framework\Http\Request\Request();
        $request->setPost(isset($_POST) ? $_POST : []);
        $request->setGet(isset($_GET) ? $_GET : []);
        $request->setFiles(isset($_FILES) ? $_FILES : []);
        $request->setMethod($_SERVER['REQUEST_METHOD']);

        unset($_POST);
        unset($_GET);
        unset($_FILES);

        $this->setRequest($request);

        $router = new Router();
        $this->setRouter($router);
    }

    public function bootstrap()
    {
        //
    }

    /**
     * @return \Framework\Http\Router\Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    public function handle()
    {
        try {
            $routeHandler = $this->getRouter()
                ->parse($_SERVER['REQUEST_URI']);

            if (!in_array($this->getRequest()->getMethod(), $routeHandler->getRegisteredRequestMethods())) {
                // TODO: implement custom exception for this
                throw new \Exception('Not implemented');
            }

            $routeHandler->setApplication($this);
            $this->response = $routeHandler->handle();
        } catch (\Exception $e) {
            $this->response = $e->getMessage();
        }

        $this->response = new Response($this->response);

        $this->response->output();

        return $this->response;
    }

    /**
     * @return \Framework\Http\Request\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    public function setRequest(Request $request) {
        $this->request = $request;

        return $this;
    }

    /**
     * @return \Framework\Http\Response\Response
     */
    public function getResponse() {
        return $this->response;
    }
}