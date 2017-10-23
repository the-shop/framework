<?php

namespace Framework\Base\Test;

use Application\CrudApi\Controller\Resource;
use Framework\Base\Application\ApplicationConfiguration;
use Framework\RestApi\Listener\Acl;
use Framework\RestApi\RestApi;
use PHPUnit\Framework\TestCase;
use Application\CrudApi\Module as CrudApiModule;
use Framework\Base\Application\BaseApplication;
use Framework\Base\Request\RequestInterface;
use Framework\Http\Request\Request;
use Framework\RestApi\Module as RestApiModule;

/**
 * All tests should extend this class
 *
 * Class UnitTest
 * @package Framework\BaseTest
 */
class UnitTest extends TestCase
{
    /**
     * @var RestApi|null
     */
    private $application = null;

    /**
     * @var array
     */
    private $authModel = [];

    /**
     * @var array
     */
    private $fields = [];

    /**
     * UnitTest constructor.
     *
     * @param null   $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $appConfig = new ApplicationConfiguration();
        $appConfig->setRegisteredModules(
            [
                RestApiModule::class,
                CrudApiModule::class
            ]
        );

        $this->application = new RestApi($appConfig);

        // Remove render events from the application
        $this->application->removeEventListeners(BaseApplication::EVENT_APPLICATION_RENDER_RESPONSE_PRE);
        $this->application->removeEventListeners(BaseApplication::EVENT_APPLICATION_RENDER_RESPONSE_POST);

        $this->application->removeEventListeners(Resource::EVENT_CRUD_API_RESOURCE_CREATE_POST);

        $this->application->listen(
            BaseApplication::EVENT_APPLICATION_HANDLE_REQUEST_PRE,
            Acl::class
        );
    }

    /**
     * @param \Framework\Base\Request\RequestInterface $request
     *
     * @return \Framework\RestApi\RestApi|null
     */
    public function runApplication(RequestInterface $request)
    {
        $app = $this->application;

        $app->setRequest($request);

        $app->setRenderer(new MemoryRenderer());

        try {
            $app->run();
        } catch (\Exception $e) {
            $app->getExceptionHandler()
                ->handle($e);
        }

        return $app;
    }

    /**
     * @param string $method
     * @param string $path
     * @param array  $parameters
     * @param array  $files
     *
     * @return \Framework\Base\Response\ResponseInterface
     */
    public function makeHttpRequest(
        string $method,
        string $path,
        array $parameters = [],
        array $files = []
    ) {
        // Normalize input
        $method = strtoupper($method);

        // Build basic http request
        $request = new Request();
        $request->setMethod($method)
                ->setUri($path);

        switch ($method) {
            case 'DELETE':
            case 'PATCH':
            case 'PUT':
            case 'POST':
                $request->setPost($parameters);
                $request->setFiles($files);
                break;

            default:
                $request->setQuery($parameters);
        }

        return $this->runApplication($request)
                    ->getResponse();
    }

    /**
     * @return RestApi|null
     */
    protected function getApplication()
    {
        if ($this->application === null) {
            $this->application->buildRequest();
        }

        return $this->application;
    }

    /**
     * Register Test Adapter, Repository, Resource, Model Fields, Auth Model
     *
     * @return void
     */
    public function loadTestClasses()
    {
        $this->authModel['tests'] = ['strategy' => 'Password', 'credentials' => ['email', 'password']];
        $this->fields['tests'] = ["email" => ["label" => "Email", "type" => "string",],
                                  "password" => ["label" => "Password", "type" => "password"],
                                  "role" => ["label" => "Role", "type" => "string"]];
        $repository = [TestModel::class => TestRepository::class];
        $resource = ['tests' => TestRepository::class];

        $adapter = new TestDatabaseAdapter();

        $this->getApplication()
             ->getRepositoryManager()
             ->addModelAdapter('tests', $adapter)
             ->setPrimaryAdapter('tests', $adapter)
             ->registerRepositories($repository)
             ->registerResources($resource)
             ->registerModelFields($this->fields)
             ->addAuthenticatableModels($this->authModel);
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function getAuthModel()
    {
        return $this->authModel;
    }
}
