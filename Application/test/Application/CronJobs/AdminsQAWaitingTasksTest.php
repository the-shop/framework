<?php

namespace Application\Test\Application\CronJobs;

use Application\CronJobs\AdminsQAWaitingTasks;
use Application\Services\SlackApiClient;
use Application\Services\SlackService;
use Application\Test\Application\DummyCurlClient;
use Framework\Base\Model\BrunoInterface;
use Framework\Base\Test\UnitTest;

class AdminsQAWaitingTasksTest extends UnitTest
{
    /** @var  BrunoInterface */
    private $user;
    /** @var  BrunoInterface */
    private $project;
    /** @var  BrunoInterface */
    private $task;

    public function setUp()
    {
        parent::setUp();
        $now = time();
        $tommorrow = $now + (24 * 60 * 60);
        $yesterday = $now - (24 * 60 * 60);
        $models = [
            'task' => [
                'submitted_for_qa' => true,
                'skillset' => [],
                'estimatedHours' => 6.0,
                'title' => 'testTask',
                'task_history' => [
                    [
                        'status' => 'qa_ready',
                        'timestamp' => $yesterday,
                    ]
                ],
                'due_date' => $tommorrow,
                'sprint_id' => 0,
            ],
            'project' => [
                'name' => 'testProject',
                'start' => $yesterday - (60 * 60),
                'end' => $tommorrow,

            ],
            'user' => [
                'name' => 'test',
                'email' => 'test@test.com',
                'password' => 'test123',
                'slack' => 'test',
            ],
        ];

        $manager = $this->getApplication()
                        ->getRepositoryManager();

        $this->user = $manager->getRepositoryFromResourceName('users')
                              ->newModel()
                              ->setAttributes($models['user'])
                              ->save();

        $this->project = $manager->getRepositoryFromResourceName('projects')
                                 ->newModel()
                                 ->setAttributes($models['project'])
                                 ->setAttribute('acceptedBy', $this->user->getId())
                                 ->save();

        $this->task = $manager->getRepositoryFromResourceName('tasks')
                              ->newModel()
                              ->setAttributes($models['task'])
                              ->setAttribute('project_id', $this->project->getId())
                              ->save();

        $this->getApplication()
             ->getConfiguration()
             ->setPathValue('internal.slack.priorityToMinutesDelay.0', 0)
             ->setPathValue('env.WEB_DOMAIN', 'testDomain');
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

        /** @var SlackService $service */
        $service = $this->getApplication()
                        ->getService(SlackService::class)
                        ->setApiClient($apiClient);

        $cronJob = new AdminsQAWaitingTasks($arr);
        $cronJob->setApplication($this->getApplication())
                ->execute();

        $slackMessage = $this->getApplication()
                             ->getRepositoryManager()
                             ->getRepositoryFromResourceName('slackMessages')
                             ->loadOneBy(['recipient' => $this->user->getAttribute('name')]);

        $this::assertStringStartsWith(
            'Hey, these tasks are *submitted for QA yesterday* and waiting for review: *testTask',
            $slackMessage->getAttribute('message')
        );
        $this::assertEquals(SlackService::HIGH_PRIORITY, $slackMessage->getAttribute('priority'));
        $this::assertEquals(false, $slackMessage->getAttribute('sent'));


        $this->delete($this->user);
        $this->delete($this->task);
        $this->delete($this->project);
        $this->delete($slackMessage);
    }

    /**
     * Deletes test record from db
     */
    private function delete(BrunoInterface $model)
    {
        $repository = $this->getApplication()
                           ->getRepositoryManager()
                           ->getRepositoryFromResourceName($model->getCollection());

        $repository->getPrimaryAdapter()
                   ->getClient()
                   ->selectCollection(
                       $model->getDatabase(),
                       $model->getCollection()
                   )
                   ->drop();
    }
}
