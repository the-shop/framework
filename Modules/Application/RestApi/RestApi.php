<?php

namespace Modules\Application\RestApi;

use Modules\Base\Module\ModuleInterface;
use Modules\Http\Request\Request;
use Modules\Http\Response\Response;
use Modules\Http\Router\Router;

class RestApi implements ModuleInterface
{
    private $request = null;
    private $response = null;

    public function __construct()
    {
        $request = new \Modules\Http\Request\Request();
        $request->setPost(isset($_POST) ? $_POST : []);
        $request->setGet(isset($_GET) ? $_GET : []);
        $request->setFiles(isset($_FILES) ? $_FILES : []);
        $request->setMethod($_SERVER['REQUEST_METHOD']);

        unset($_POST);
        unset($_GET);
        unset($_FILES);

        $this->setRequest($request);
    }

    public function bootstrap()
    {
        //
    }

    public function handle()
    {
        try {
            $router = new Router();
            $routeHandler = $router->parse($_SERVER['REQUEST_URI']);

            if (!in_array($this->getRequest()->getMethod(), $routeHandler->getRegisteredRequestMethods())) {
                // throw
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
     * @return \Modules\Http\Request\Request
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
     * @return \Modules\Http\Response\Response
     */
    public function getResponse() {
        return $this->response;
    }
}