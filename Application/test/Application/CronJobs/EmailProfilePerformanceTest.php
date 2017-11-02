<?php

namespace Application\Test\Application\CronJobs;

use Application\CronJobs\EmailProfilePerformance;
use Application\Services\EmailService;
use Application\Test\Application\Traits\Helpers;
use Application\Test\Application\Traits\ProfileRelated;
use Application\Test\Application\Traits\ProjectRelated;
use Framework\Base\Test\Mailer\DummyMailer;
use Framework\Base\Test\Mailer\DummyMailerClient;
use Framework\Base\Test\UnitTest;

class EmailProfilePerformanceTest extends UnitTest
{
    use ProjectRelated, ProfileRelated, Helpers;

    public function setUp()
    {
        parent::setUp();
        $newUser = $this->getApplication()->getRepositoryManager()
            ->getRepositoryFromResourceName('users')
            ->newModel()
            ->setAttributes([
                'name' => 'test user',
                'email' => $this->generateRandomEmail(20),
                'password' => 'test',
                'skills' => ['PHP'],
                'xp' => 200,
                'employeeRole' => 'Apprentice',
                'minimumsMissed' => 0,
                'employee' => true,
                'slack' => $this->generateRandomString(),
                'active' => true,
                'admin' => true,
            ])
            ->save();

        $this->setTaskOwner($newUser);
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->purgeCollection('users');
    }

    public function testEmailProfilePerformanceToUserWithoutAccountantsFlag()
    {
        $mailerSettings = [
            EmailService::class => [
                'mailerInterface' => DummyMailer::class,
                'mailerClient' => [
                    'classPath' => DummyMailerClient::class,
                    'constructorArguments' => [],
                ],
            ],
        ];

        $app = $this->getApplication();
        $app->getConfiguration()->setPathValue('servicesConfig', $mailerSettings);

        $cronJob = new EmailProfilePerformance(['timer' => '', 'args' => ['daysAgo' => 7]]);
        $cronJob->setApplication($app);
        $out = $cronJob->execute();

        $this->assertEquals('CronJob successfully done!', $out);
    }

    public function testEmailProfilePerformanceToUserWithAccountantsFlag()
    {
        $mailerSettings = [
            EmailService::class => [
                'mailerInterface' => DummyMailer::class,
                'mailerClient' => [
                    'classPath' => DummyMailerClient::class,
                    'constructorArguments' => [],
                ],
            ],
        ];

        $app = $this->getApplication();
        $app->getConfiguration()->setPathValue('servicesConfig', $mailerSettings);

        $cronJob = new EmailProfilePerformance([
            'timer' => '',
            'args' => [
                'daysAgo' => 7,
                'accountants' => true,
                ]
        ]);
        $cronJob->setApplication($app);
        $out = $cronJob->execute();

        $this->assertEquals('CronJob successfully done!', $out);
    }
}
