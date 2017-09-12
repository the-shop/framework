<?php

namespace Framework\Base\Test;

use Framework\Base\Application\ApplicationInterface;
use Framework\RestApi\Module;
use Framework\RestApi\RestApi;
use PHPUnit\Framework\TestCase;
use Application\CrudApi\Module as CrudApiModule;
use Framework\Base\Application\BaseApplication;
use Framework\Base\Request\RequestInterface;
use Framework\Http\Request\Request;
use Framework\RestApi\Module as RestApiModule;
use Framework\Terminal\Render\Memory as MemoryRenderer;

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

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->application = new RestApi([
            RestApiModule::class,
            CrudApiModule::class
        ]);

        // Remove render events from the application
        $this->application->removeEventListeners(BaseApplication::EVENT_APPLICATION_RENDER_RESPONSE_PRE);
        $this->application->removeEventListeners(BaseApplication::EVENT_APPLICATION_RENDER_RESPONSE_POST);
    }

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
     * @param array $parameters
     * @param array $files
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
            $this->application = new RestApi([
                Module::class
            ]);

            $this->application->buildRequest();
        }

        return $this->application;
    }

    /**
     * @param ApplicationInterface|null $application
     * @return ApplicationInterface|RestApi|null
     */
    protected function setApplication(ApplicationInterface $application = null)
    {
        if ($application === null) {
            $this->application = new RestApi();
        } else {
            $this->application = $application;
        }

        return $this->application;
    }
}
