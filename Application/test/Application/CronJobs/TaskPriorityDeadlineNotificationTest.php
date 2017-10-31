<?php

namespace Application\Test\Application\CronJobs;

use Application\CronJobs\TaskPriorityDeadlineNotification;
use Application\Services\SlackApiClient;
use Application\Services\SlackService;
use Application\Test\Application\DummyCurlClient;
use Application\Test\Application\Traits\Helpers;
use Application\Test\Application\Traits\ProjectRelated;
use Framework\Base\Test\UnitTest;

class TaskPriorityDeadlineNotificationTest extends UnitTest
{
    use ProjectRelated, Helpers;

    /** @var  \Framework\Base\Model\BrunoInterface */
    private $task;
    /** @var  \Framework\Base\Model\BrunoInterface */
    private $user;
    /** @var  \Framework\Base\Model\BrunoInterface */
    private $project;
    /** @var  \Framework\Base\Model\BrunoInterface */
    private $slackMessage;

    public function setUp()
    {
        parent::setUp();
        $now = time();
        $this->user = $this->getApplication()
                           ->getRepositoryManager()
                           ->getRepositoryFromResourceName('users')
                           ->newModel()
                           ->setAttributes(
                               [
                                   'name' => 'test user',
                                   'email' => $this->generateRandomEmail(20),
                                   'password' => 'test',
                                   'slack' => 'test',
                                   'active' => true,
                                ]
                           )
                           ->save();

        $this->project = $this->getNewProject()
                              ->setAttribute('acceptedBy', $this->user->getId())
                              ->save();

        $this->task = $this->getNewTask()
                           ->setAttribute('due_date', ($now + (3 * 24 * 60 * 60)))
                           ->setAttribute('project_id', $this->project->getId())
                           ->setAttribute('owner', null)
                           ->setAttribute('priority', 'High')
                           ->save();

        $this->getApplication()
             ->getConfiguration()
             ->setPathValue('internal.slack.priorityToMinutesDelay.0', 0);
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->deleteCollection($this->task);
        $this->deleteCollection($this->project);
        $this->deleteCollection($this->user);
        $this->deleteCollection($this->slackMessage);
    }

    public function testExecute()
    {
        $arr = [
            'timer' => 'everyMinute',
            'args' => [],
        ];

        $apiClient = new SlackApiClient();
        $apiClient->setClient(new DummyCurlClient())
                  ->setApplication($this->getApplication());

        $service = $this->getApplication()
                        ->getService(SlackService::class)
                        ->setApiClient($apiClient);

        $cronJob = new TaskPriorityDeadlineNotification($arr);
        $cronJob->setApplication($this->getApplication())
                ->execute();

        $repository = $this->getApplication()
                           ->getRepositoryManager()
                           ->getRepositoryFromResourceName('slackMessages');

        $query = $repository->createNewQueryForModel($repository->newModel())
                            ->addAndCondition(
                                'recipient',
                                '=',
                                $this->user->getAttribute('slack')
                            );
        $slackMessages = array_values($repository->loadMultiple($query));

        $this->slackMessage = $slackMessages[0];

        $this::assertStringStartsWith(
            'On project *Test Project*, there are *1* tasks with *High priority* in next *7 days*',
            $this->slackMessage->getAttribute('message')
        );
        $this::assertEquals(
            SlackService::LOW_PRIORITY,
            $this->slackMessage->getAttribute('priority')
        );
        $this::assertEquals(false, $this->slackMessage->getAttribute('sent'));
    }
}
