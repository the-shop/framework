<?php

namespace Application\Test\Application\Listeners;

use Application\Listeners\TaskBlocked;
use Application\Services\SlackApiClient;
use Application\Services\SlackService;
use Application\Test\Application\DummyCurlClient;
use Application\Test\Application\Traits\Helpers;
use Application\Test\Application\Traits\ProjectRelated;
use Framework\Base\Test\UnitTest;

class TaskBlockedTest extends UnitTest
{
    use ProjectRelated, Helpers;

    /** @var  \Framework\Base\Model\BrunoInterface */
    private $task;
    /** @var  \Framework\Base\Model\BrunoInterface */
    private $project;
    /** @var  \Framework\Base\Model\BrunoInterface */
    private $user;
    /** @var  \Framework\Base\Model\BrunoInterface */
    private $slackMessage;

    public function setUp()
    {
        parent::setUp();

        $this->user = $this->getApplication()
                           ->getRepositoryManager()
                           ->getRepositoryFromResourceName('users')
                           ->newModel()
                           ->setAttributes(
                               [
                                   'name' => 'test user',
                                   'email' => $this->generateRandomEmail(20),
                                   'password' => 'test',
                                   'skills' => ['PHP'],
                                   'xp' => 200,
                                   'employeeRole' => 'Apprentice',
                                   'slack' => 'test'
                               ]
                           )
                           ->save();

        $this->project = $this->getNewProject()
                              ->setAttribute('acceptedBy', $this->user->getId())
                              ->save();

        $this->task = $this->getNewTask()
                           ->setAttribute('project_id', $this->project->getId())
                           ->save();
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->deleteCollection($this->project);
        $this->deleteCollection($this->user);
        $this->deleteCollection($this->task);
        $this->deleteCollection($this->slackMessage);
    }

    public function testHandle()
    {
        $this->task->setAttribute('blocked', true);

        $listener = new TaskBlocked();
        $listener->setApplication($this->getApplication());

        $apiClient = new SlackApiClient();
        $apiClient->setClient(new DummyCurlClient())
                  ->setApplication($this->getApplication());

        $service = $this->getApplication()
                        ->getService(SlackService::class)
                        ->setApiClient($apiClient);

        $this::assertEquals(true, $listener->handle($this->task));

        $this->slackMessage = $this->getApplication()
                                   ->getRepositoryManager()
                                   ->getRepositoryFromResourceName('slackMessages')
                                   ->loadOneBy(['recipient' => $this->user->getAttribute('slack')]);

        $this::assertStringStartsWith(
            'Hey, task *test task* is currently blocked!',
            $this->slackMessage->getAttribute('message')
        );
        $this::assertEquals(
            SlackService::HIGH_PRIORITY,
            $this->slackMessage->getAttribute('priority')
        );
        $this::assertEquals(false, $this->slackMessage->getAttribute('sent'));
    }
}
