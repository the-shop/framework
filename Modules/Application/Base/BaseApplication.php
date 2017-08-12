<?php

namespace Framework\Application\Base;

use Framework\Base\Di\Resolver;
use Framework\Base\Model\RepositoryManager;
use Framework\Base\Model\RepositoryManagerInterface;
use Framework\Http\Request\Request;
use Framework\Http\Response\Response;
use Framework\Http\Router\Router;

/**
 * Class BaseApplication
 * @package Framework\Application\Base
 */
abstract class BaseApplication
{
    /**
     * @var Router|null
     */
    private $router = null;

    /**
     * @var Request|null
     */
    private $request = null;

    /**
     * @var Response|null
     */
    private $response = null;

    /**
     * @var RepositoryManager|null
     */
    private $repositoryManager = null;

    /**
     * @var Resolver|null
     */
    private $resolver = null;

    /**
     * BaseApplication constructor.
     */
    public function __construct()
    {
        $this->bootstrap();
    }

    /**
     * @return Bootstrap
     */
    public function bootstrap()
    {
        $bootstrap = new Bootstrap();
        $registerModules = [
            \Framework\User\Api\Module::class
        ];
        $bootstrap->registerModules($registerModules, $this);

        return $bootstrap;
    }

    /**
     * @param RepositoryManagerInterface $repositoryManager
     * @return $this
     */
    public function setRepositoryManager(RepositoryManagerInterface $repositoryManager)
    {
        $this->repositoryManager = $repositoryManager;

        return $this;
    }

    /**
     * @return \Framework\Base\Model\RepositoryManagerInterface
     */
    public function getRepositoryManager()
    {
        if ($this->repositoryManager === null) {
            $repositoryManager = $this->getResolver()
                ->resolve(RepositoryManager::class);
            $this->setRepositoryManager($repositoryManager);
        }

        return $this->repositoryManager;
    }

    /**
     * @return \Framework\Http\Router\Router
     */
    public function getRouter()
    {
        if ($this->router === null) {
            $router = $this->getResolver()
                ->resolve(Router::class);
            $this->setRouter($router);
        }

        return $this->router;
    }

    /**
     * @param Router $router
     * @return $this
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;

        return $this;
    }

    /**
     * @return \Framework\Http\Request\Request
     */
    public function getRequest()
    {
        if ($this->request === null) {
            $request = $this->getResolver()
                ->resolve(Request::class);

            $request->setPost(isset($_POST) ? $_POST : []);
            $request->setGet(isset($_GET) ? $_GET : []);
            $request->setFiles(isset($_FILES) ? $_FILES : []);
            $request->setMethod($_SERVER['REQUEST_METHOD']);

            unset($_POST);
            unset($_GET);
            unset($_FILES);

            $this->setRequest($request);
        }

        return $this->request;
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request) {
        $this->request = $request;

        return $this;
    }

    /**
     * @param Response $response
     * @return $this
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return \Framework\Http\Response\Response
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * @return Resolver
     */
    public function getResolver()
    {
        if ($this->resolver === null) {
            $this->resolver = new Resolver();
        }
        return $this->resolver;
    }
}
