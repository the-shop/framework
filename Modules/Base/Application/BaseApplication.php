<?php

namespace Framework\Base\Application;

use Framework\Base\Di\Resolver;
use Framework\Base\Manager\Repository;
use Framework\Base\Manager\RepositoryInterface;
use Framework\Http\Request\Request;
use Framework\Http\Response\Response;
use Framework\Http\Router\Router;
use Framework\User\Api\Module;

/**
 * Class BaseApplication
 * @package Framework\Base\Application
 */
abstract class BaseApplication implements ApplicationInterface
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
     * @var RepositoryInterface|null
     */
    private $repositoryManager = null;

    /**
     * @var Resolver|null
     */
    private $resolver = null;

    /**
     * @return mixed
     */
    abstract public function run();

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
            Module::class
        ];
        $bootstrap->registerModules($registerModules, $this);

        return $bootstrap;
    }

    /**
     * @param \Framework\Base\Manager\RepositoryInterface $repositoryManager
     * @return $this
     */
    public function setRepositoryManager(RepositoryInterface $repositoryManager)
    {
        $this->repositoryManager = $repositoryManager;

        return $this;
    }

    /**
     * @return \Framework\Base\Manager\RepositoryInterface|null
     */
    public function getRepositoryManager()
    {
        if ($this->repositoryManager === null) {
            $repositoryManager = $this->getResolver()
                ->resolve(Repository::class);
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
