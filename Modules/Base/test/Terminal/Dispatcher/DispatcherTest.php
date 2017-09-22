<?php

namespace Framework\Base\Test\Terminal\Dispatcher;

use Framework\Base\Terminal\Commands\TestCommand;
use Framework\Base\Terminal\Router\Dispatcher;
use Framework\Base\Test\UnitTest;
use Framework\Http\Request\Request;

/**
 * Class DispatcherTest
 * @package Framework\Base\Test\Terminal\Dispatcher
 */
class DispatcherTest extends UnitTest
{
    /**
     * @var array
     */
    private $routes = [
        'test' => [
            'handler' => TestCommand::class,
            'requiredParams' => [
                'testParam',
                'testParam2',
            ],
            'optionalParams' => [
                'testOptionalParam',
                'testOptionalParam2',
            ],
        ],
    ];

    /**
     * Test dispatcher parse request - no command name registered - exception
     */
    public function testDispatcherParseRequestCommandNameNotRegistered()
    {
        $app = $this->getApplication();
        $app->setDispatcher(new Dispatcher());
        $dispatcher = $app->getDispatcher();
        $dispatcher->addRoutes($this->routes);

        $request = new Request();
        $serverInfo = $_SERVER;
        $serverInfo['argv'] = [
            'yoda.php',
            'testCommand',
            'testParam=testParam',
            '[optionalPARAM=optional]',
        ];

        $request->setServer($serverInfo);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Command name testCommand is not registered.');
        $this->expectExceptionCode(404);

        $dispatcher->parseRequest($request);
    }

    /**
     * Test dispatcher parse request - invalid required param - exception
     */
    public function testDispatcherParseRequestInvalidRequiredParams()
    {
        $app = $this->getApplication();
        $app->setDispatcher(new Dispatcher());
        $dispatcher = $app->getDispatcher();
        $dispatcher->addRoutes($this->routes);

        $request = new Request();
        $serverInfo = $_SERVER;
        $serverInfo['argv'] = [
            'yoda.php',
            'test',
            'testParam=testParam',
            'testingwron=wrong',
            '[optionalPARAM=optional]',
        ];

        $request->setServer($serverInfo);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid required arguments');
        $this->expectExceptionCode(403);

        $dispatcher->parseRequest($request);
    }

    /**
     * Test dispatcher parse request - invalid optional params - exception
     */
    public function testDispatcherParseRequestInvalidOptionalParams()
    {
        $app = $this->getApplication();
        $app->setDispatcher(new Dispatcher());
        $dispatcher = $app->getDispatcher();
        $dispatcher->addRoutes($this->routes);

        $request = new Request();
        $serverInfo = $_SERVER;
        $serverInfo['argv'] = [
            'yoda.php',
            'test',
            'testParam=testParam',
            'testParam2=testParam',
            '[optionalPARAMWrong=optional]',
        ];

        $request->setServer($serverInfo);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid optional arguments');
        $this->expectExceptionCode(403);

        $dispatcher->parseRequest($request);
    }

    /**
     * Test dispatcher parse request - success
     */
    public function testDispatcherParseRequestSuccess()
    {
        $app = $this->getApplication();
        $app->setDispatcher(new Dispatcher());
        $dispatcher = $app->getDispatcher();
        $dispatcher->addRoutes($this->routes);

        $request = new Request();
        $serverInfo = $_SERVER;
        $serverInfo['argv'] = [
            'yoda.php',
            'test',
            'testParam=testParam',
            'testParam2=testParam2',
            '[testOptionalParam=optional]',
            '[testOptionalParam2=optional2]',
        ];

        $request->setServer($serverInfo);

        $dispatcher->parseRequest($request);

        $this->assertEquals(
            [
                'testparam' => 'testParam',
                'testparam2' => 'testParam2',
                'testoptionalparam' => 'optional',
                'testoptionalparam2' => 'optional2',
            ],
            $dispatcher->getRouteParameters()
        );
        $this->assertEquals(
            TestCommand::class,
            $dispatcher->getHandler()
        );
    }
}
