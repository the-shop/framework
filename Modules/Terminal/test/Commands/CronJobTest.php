<?php

namespace Framework\Terminal\Test\Commands;

use Framework\Terminal\Commands\Cron\CronJobInterface;
use Framework\Terminal\Commands\CronJobsScheduler;
use Framework\Terminal\Router\Dispatcher;
use Framework\Base\Test\UnitTest;
use Framework\Terminal\Test\TestJob;

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

    private $cronJobs = [
        TestJob::class => [
            'value' => 'daily',
            'args' => [],
        ],
    ];

    /**
     * Test cron job add job - success
     */
    public function testAddCronJob()
    {
        $app = $this->getApplication();
        $app->setDispatcher(new Dispatcher());
        $dispatcher = $app->getDispatcher();
        $dispatcher->addRoutes($this->routes);

        $handler = new CronJobsScheduler();
        $handler->setApplication($app);

        $cronJob = new TestJob(reset($this->cronJobs));

        $handler->addCronJob($cronJob);

        $this::assertContainsOnlyInstancesOf(CronJobInterface::class, $handler->getRegisteredJobs());

        return $handler;
    }

    /**
     * Test cron jobs params being set correctly
     *
     * @depends testAddCronJob
     */
    public function testCronJobSetters(CronJobsScheduler $handler)
    {
        $app = $this->getApplication();
        $app->setDispatcher(new Dispatcher());
        $dispatcher = $app->getDispatcher();
        $dispatcher->addRoutes($this->routes);

        /**
         * @var CronJobInterface $cronJob
         */
        $cronJob = $handler->getRegisteredJobs()[0];

        $this::assertEquals(TestJob::class, $cronJob->getIdentifier());
        $this::assertEquals('0 0 * * *', $cronJob->getCronTimeExpression());

        /**
         * @var TestJob $cronJob
         */
        $cronJob->everyMinute();

        $this::assertEquals('* * * * *', $cronJob->getCronTimeExpression());
    }
}
