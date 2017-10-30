<?php

namespace Application\Test\Application\Listeners;

use Application\Listeners\ProfileUpdate;
use Application\Services\EmailService;
use Application\Test\Application\Traits\Helpers;
use Application\Test\Application\Traits\ProfileRelated;
use Application\Test\Application\Traits\ProjectRelated;
use Framework\Base\Test\UnitTest;
use Framework\Base\Mailer\SendGrid;
use Framework\Base\Test\Mailer\DummySendGridClient;

/**
 * Class UpdateProfileTest
 * @package Application\Test\Application\Listeners
 */
class UpdateProfileTest extends UnitTest
{
    use ProfileRelated, ProjectRelated, Helpers;

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
            ])
            ->save();

        $this->setTaskOwner($newUser);
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->purgeCollection('users');
        $this->purgeCollection('projects');
        $this->purgeCollection('tasks');
        $this->purgeCollection('sprints');
    }

    /**
     * Test profile updated - wrong payload collection - return false
     */
    public function testListenerTaskAsPayload()
    {
        $task = $this->getNewTask();

        $listener = (new ProfileUpdate())->setApplication($this->getApplication());

        $this->assertEquals(false, $listener->handle($task));
    }

    /**
     * Test profile updated Xp - email sent to user
     */
    public function testProfileUpdatedXp()
    {
        $emailServiceConfig = [
            'mailerInterface' => SendGrid::class,
            'mailerClient' => [
                'classPath' => DummySendGridClient::class,
                'constructorArguments' => [],
            ],
        ];

        $this->getApplication()
            ->getConfiguration()
            ->setPathValue('servicesConfig.' . EmailService::class, $emailServiceConfig);

        $profile = $this->profile;
        $profile->setAttribute('xp', 205);
        $listener = (new ProfileUpdate())->setApplication($this->getApplication());

        $this->assertEquals(
            'Email was successfully sent!',
            $listener->handle($profile)
        );
    }
}
