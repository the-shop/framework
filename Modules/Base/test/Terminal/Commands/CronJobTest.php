<?php

namespace Framework\Base\Test\Terminal\Commands;

use Framework\Base\Terminal\Commands\CronJob;
use Framework\Base\Terminal\Router\Dispatcher;
use Framework\Base\Test\UnitTest;
use InvalidArgumentException;

/**
 * Class CronJobTest
 * @package Framework\Base\Test\Terminal\Commands
 */
class CronJobTest extends UnitTest
{
    /**
     * @var array
     */
    private $routes = [
        'test' => [
            'handler' => DummyCommand::class,
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
     * Test cron job add job - success
     */
    public function testCronJobAddAddCronJob()
    {
        $app = $this->getApplication();
        $app->setDispatcher(new Dispatcher());
        $dispatcher = $app->getDispatcher();
        $dispatcher->addRoutes($this->routes);

        $cronJob = new CronJob();
        $cronJob->setApplication($app);

        $cronJob->addCronJob(
            'test',
            '* * * * *',
            [
                'testParam' => 'testParam',
                'testParam2' => 'testParam',
            ]
        );

        $this->assertEquals(
            [
                [
                    'commandName' => 'test',
                    'handler' => DummyCommand::class,
                    'timeExpression' => '* * * * *',
                    'parameters' => [
                        'testParam' => 'testParam',
                        'testParam2' => 'testParam',
                    ],
                ],

            ],
            $cronJob->getRegisteredJobs()
        );
    }

    /**
     * Test cron job add job - command not registered - exception
     */
    public function testCronJobAddCronJobCommandNotRegistered()
    {
        $app = $this->getApplication();
        $app->setDispatcher(new Dispatcher());
        $dispatcher = $app->getDispatcher();
        $dispatcher->addRoutes($this->routes);

        $cronJob = new CronJob();
        $cronJob->setApplication($app);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Command name commandTest is not registered.');
        $this->expectExceptionCode(404);

        $cronJob->addCronJob(
            'commandTest',
            '* * * * *',
            [
                'testParam' => 'testParam',
                'testParam2' => 'testParam',
            ]
        );
    }

    /**
     * Test cron jobs add job without enough required params - exception
     */
    public function testCronJobAddCronJobNotEnoughRequiredParams()
    {
        $app = $this->getApplication();
        $app->setDispatcher(new Dispatcher());
        $dispatcher = $app->getDispatcher();
        $dispatcher->addRoutes($this->routes);

        $cronJob = new CronJob();
        $cronJob->setApplication($app);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Not enough requiredParams passed for test command');
        $this->expectExceptionCode(403);

        $cronJob->addCronJob(
            'test',
            '* * * * *',
            [
                'testParam' => 'testParam'
            ]
        );
    }

    /**
     * Test cron job run jobs
     */
    public function testCronJobRunJobs()
    {
        $app = $this->getApplication();
        $app->setDispatcher(new Dispatcher());
        $dispatcher = $app->getDispatcher();
        $dispatcher->addRoutes($this->routes);

        $cronJob = new CronJob();
        $cronJob->setApplication($app);

        $cronJob->addCronJob(
            'test',
            '* * * * *',
            [
                'testParam' => 'testParam',
                'testParam2' => 'testParam2',
                'optionalParam' => 'optionalParam',
                'optionalParam2' => 'optionalParam2'
            ]
        );

        $out = $cronJob->runCronJobs();

        $this->assertEquals(
            [
                'test' => [
                    'COMMAND DONE! STATUS CODE 200.',
                    'Response: ' => 'testParam, testParam2, optionalParam, optionalParam2'
                ]
            ],
            $out
        );
    }
}
