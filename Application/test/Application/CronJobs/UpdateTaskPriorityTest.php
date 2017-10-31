<?php

namespace Application\Test\Application\CronJobs;

use Application\CronJobs\UpdateTaskPriority;
use Application\Services\SlackService;
use Application\Test\Application\Traits\Helpers;
use Application\Test\Application\Traits\ProfileRelated;
use Application\Test\Application\Traits\ProjectRelated;
use Framework\Base\Model\BrunoInterface;
use Framework\Base\Test\UnitTest;

class UpdateTaskPriorityTest extends UnitTest
{
    use ProfileRelated, ProjectRelated, Helpers;

    /**
     * @var BrunoInterface[]
     */
    private $taskList = [];

    /**
     * @var null|BrunoInterface
     */
    private $project = null;

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

        $project = $this->getNewProject();
        $members = [$this->profile->getAttribute('_id')];
        $project->setAttributes([
            'members' => $members,
            'acceptedBy' => $this->profile->getAttribute('_id')
        ]);
        $project->save();
        $this->project = $project;

        $sprint = $this->getNewSprint();
        $sprint->save();

        for ($i = 1; $i < 15; $i++) {
            $task = $this->getNewTask();
            $task->setAttributes([
                'ready' => true,
                'due_date' => (int)(new \DateTime())->modify("+{$i} days")->format('U'),
                'skillset' => ['PHP', 'React'],
                'project_id' => $this->project->getAttribute('_id'),
                'sprint_id' => $sprint->getAttribute('_id'),
                'priority' => 'Medium'
            ]);

            if ($i === 1
                || $i === 2
                || $i === 3
                || $i === 8
                || $i === 9
                || $i === 10
                || $i === 11
            ) {
                $task->setAttribute('priority', 'High');
            }

            $task->save();
            $this->taskList[] = $task;
        }
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->purgeCollection('projects');
        $this->purgeCollection('tasks');
        $this->purgeCollection('sprints');
        $this->purgeCollection('users');
    }

    public function testUpdateTaskPriority()
    {
        $message =
            'On project *'
            . $this->project->getAttribute('name')
            . '*, there are *'
            . 4
            . '* tasks bumped to *High priority* '
            . 'and *'
            . 4
            . '* bumped to *Medium priority*';

        $cronJob = new UpdateTaskPriority(['timer' => '', 'args' => []]);
        $cronJob->setApplication($this->getApplication());
        $cronJob->execute();

        $slackMessage = $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('slackMessages')
            ->loadOneBy(['recipient' => $this->profile->getAttribute('slack')]);

        $slackMessageAttributes = $slackMessage->getAttributes();

        $this->assertArrayHasKey('recipient', $slackMessageAttributes);
        $this->assertArrayHasKey('message', $slackMessageAttributes);
        $this->assertArrayHasKey('priority', $slackMessageAttributes);
        $this->assertArrayHasKey('sent', $slackMessageAttributes);
        $this->assertArrayHasKey('runAt', $slackMessageAttributes);
        $this->assertEquals(
            $slackMessageAttributes['recipient'],
            $this->profile->getAttribute('slack')
        );
        $this->assertEquals($slackMessageAttributes['priority'], SlackService::LOW_PRIORITY);
        $this->assertEquals($slackMessageAttributes['sent'], false);
        $this->assertEquals($slackMessageAttributes['message'], $message);

        $updatedTasks = $this->getApplication()->getRepositoryManager()
            ->getRepositoryFromResourceName('tasks')
            ->loadMultiple();

        $counter = 1;
        foreach ($updatedTasks as $task) {
            if ($counter <= 7) {
                $this->assertEquals('High', $task->getAttribute('priority'));
            } else {
                $this->assertEquals('Medium', $task->getAttribute('priority'));
            }
            $counter++;
        }
    }
}
