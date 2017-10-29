<?php

namespace Framework\Terminal\Test\Commands;

use Framework\Base\Application\ApplicationInterface;
use Framework\Terminal\Commands\Cron\CronJobInterface;
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
            'timer' => 'daily',
            'args' => []
        ],
    ];

    /**
     * Test cron job add job - success
     */
    public function testAddCronJob()
    {
        $app = $this->getApplication();
        $app->setDispatcher($dispatcher = new Dispatcher());
        $dispatcher->addRoutes($this->routes);

        $cronJob = new TestJob(reset($this->cronJobs));

        $app->registerCronJob($cronJob);

        $this::assertContainsOnlyInstancesOf(CronJobInterface::class, $app->getRegisteredCronJobs());

        return $app;
    }

    /**
     * Test cron jobs params being set correctly
     *
     * @depends testAddCronJob
     */
    public function testCronJobSetters(ApplicationInterface $app)
    {
        $dispatcher = $app->getDispatcher();
        $dispatcher->addRoutes($this->routes);

        /**
         * @var CronJobInterface $cronJob
         */
        foreach ($app->getRegisteredCronJobs() as $registeredCronJob) {
            if ($registeredCronJob instanceof TestJob) {
                $cronJob = $registeredCronJob;
            }
        }

        $this::assertEquals('0 0 * * *', $cronJob->getCronTimeExpression());

        /**
         * @var TestJob $cronJob
         */
        $cronJob->everyMinute();

        $this::assertEquals('* * * * *', $cronJob->getCronTimeExpression());
    }
}
