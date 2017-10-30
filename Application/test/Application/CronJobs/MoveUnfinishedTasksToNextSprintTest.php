<?php

namespace Application\Test\Application\CronJobs;

use Application\CronJobs\MoveUnfinishedTasksToNextSprint;
use Application\Services\SlackService;
use Application\Test\Application\Traits\Helpers;
use Application\Test\Application\Traits\ProfileRelated;
use Application\Test\Application\Traits\ProjectRelated;
use Framework\Base\Model\BrunoInterface;
use Framework\Base\Test\UnitTest;

class MoveUnfinishedTasksToNextSprintTest extends UnitTest
{
    use ProfileRelated, ProjectRelated, Helpers;

    /**
     * @var BrunoInterface[]
     */
    private $taskList = [];

    /**
     * @var BrunoInterface[]
     */
    private $futureSprintList = [];

    /**
     * @var BrunoInterface[]
     */
    private $endedSprintList = [];

    /**
     * @var null|BrunoInterface
     */
    private $project = null;

    /**
     * @var null|BrunoInterface
     */
    private $projectWithoutFutureSprints = null;

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
            'acceptedBy' => $this->profile->getAttribute('_id'),
        ]);
        $project->save();
        $this->project = $project;

        $projectWithoutFutureSprints = $this->getNewProject();
        $members = [$this->profile->getAttribute('_id')];
        $projectWithoutFutureSprints->setAttributes([
            'members' => $members,
            'acceptedBy' => $this->profile->getAttribute('_id'),
        ]);
        $projectWithoutFutureSprints->save();
        $this->projectWithoutFutureSprints = $projectWithoutFutureSprints;

        // Generate sprints and tasks
        for ($i = 1; $i < 4; $i++) {
            // Future sprint
            $sprint = $this->getNewSprint();
                $start = (new \DateTime())->modify("+{$i} days")->format('U');
                $e = $i + 1;
                $end = (new \DateTime())->modify("+{$e} day")->format('U');
                $sprint->setAttributes([
                    'start' => $start,
                    'end' => $end,
                    'project_id' => $this->project->getId()
                ]);
            $sprint->save();
            $this->futureSprintList[$sprint->getAttribute('start')] = $sprint;

            // Ended sprint
            $endedSprint = $this->getNewSprint();
            $s = $i + 2;
            $endedSprintStart = (new \DateTime())->modify("-{$s} days")->format('U');
            $endedSprintEnd = (new \DateTime())->modify("-{$i} day")->format('U');
            $endedSprint->setAttributes([
                'start' => $endedSprintStart,
                'end' => $endedSprintEnd,
                'project_id' => $i === 1 ?
                    $projectWithoutFutureSprints->getId() : $this->project->getId()
            ]);
            $endedSprint->save();
            $this->endedSprintList[$sprint->getAttribute('start')] = $sprint;

            $task = $this->getNewTask();
            $task->setAttributes([
                'ready' => true,
                'due_date' => (int)(new \DateTime())->modify("-{$s} days")->format('U'),
                'skillset' => ['PHP', 'React'],
                'project_id' => $this->project->getAttribute('_id'),
                'sprint_id' => $endedSprint->getAttribute('_id'),
                'priority' => 'Medium',
            ]);

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

    public function testMoveUnfinishedTasksToNearestFutureSprint()
    {
        $cronJob = new MoveUnfinishedTasksToNextSprint(['timer' => '', 'args' => []]);
        $cronJob->setApplication($this->getApplication());
        $cronJob->execute();

        $tasks = $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('tasks')
            ->loadMultiple();

        foreach ($tasks as $task) {
            $this->assertEquals(
                $task->getAttribute('sprint_id'),
                min($this->futureSprintList)->getAttribute('_id')
            );
        }
    }

    public function testMessageAdminAboutMissingFutureSprintsToMoveTask()
    {
        $cronJob = new MoveUnfinishedTasksToNextSprint(['timer' => '', 'args' => []]);
        $cronJob->setApplication($this->getApplication());
        $cronJob->execute();

        $slackMessage = $this->getApplication()->getRepositoryManager()
            ->getRepositoryFromResourceName('slackMessages')
            ->loadOneBy(['recipient' => '@' . $this->profile->getAttribute('slack')]);
        $slackMessageAttributes = $slackMessage->getAttributes();

        $this->assertArrayHasKey('recipient', $slackMessageAttributes);
        $this->assertArrayHasKey('message', $slackMessageAttributes);
        $this->assertArrayHasKey('priority', $slackMessageAttributes);
        $this->assertArrayHasKey('sent', $slackMessageAttributes);
        $this->assertArrayHasKey('runAt', $slackMessageAttributes);
        $this->assertEquals(
            $slackMessageAttributes['recipient'],
            '@' . $this->profile->getAttribute('slack')
        );

        $message =
            'Hey! There are no future sprints created to move unfinished tasks '
            . 'from ended sprints on project : *'
            . $this->projectWithoutFutureSprints->getAttribute('name')
            . '*';

        $this->assertEquals($slackMessageAttributes['priority'], SlackService::LOW_PRIORITY);
        $this->assertEquals($slackMessageAttributes['sent'], false);
        $this->assertEquals($slackMessageAttributes['message'], $message);
    }
}
