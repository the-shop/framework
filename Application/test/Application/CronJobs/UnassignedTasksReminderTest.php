<?php

namespace Application\Test\Application\CronJobs;

use Application\CronJobs\UnassignedTasksReminder;
use Application\Services\SlackService;
use Application\Test\Application\Traits\Helpers;
use Application\Test\Application\Traits\ProfileRelated;
use Application\Test\Application\Traits\ProjectRelated;
use Framework\Base\Model\BrunoInterface;
use Framework\Base\Test\UnitTest;

class UnassignedTasksReminderTest extends UnitTest
{
    use ProjectRelated, ProfileRelated, Helpers;

    /**
     * @var null|BrunoInterface
     */
    private $project = null;

    private $taskList = [];

    private $sprintList = [];

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
            ])
            ->save();

        $this->setTaskOwner($newUser);

        $project = $this->getNewProject();
        $members = [$this->profile->getAttribute('_id')];
        $project->setAttribute('members', $members);
        $project->setAttribute('acceptedBy', $this->profile->getAttribute('_id'));
        $project->save();
        $this->project = $project;

        for ($i = 1; $i < 6; $i++) {
            $sprint = $this->getNewSprint();
            $sprint->setAttribute('project_id', $this->project->getAttribute('_id'));
            if ($i === 1 || $i === 2 || $i === 3) {
                $sprint->setAttribute('start', (int)(new \DateTime())->modify("-{$i} days")
                    ->format('U'));
            } else {
                $sprint->setAttribute('start', (int)(new \DateTime())->modify("+{$i} day")
                    ->format('U'));
            }

            $sprint->setAttribute('end', (int)(new \DateTime())->modify("+{$i} day")
                ->format('U'));
            $sprint->save();
            $this->sprintList[] = $sprint;
        }

        for ($i = 1; $i < 6; $i++) {
            $task = $this->getNewTask();
            $task->setAttributes([
                'project_id' => $this->project->getAttribute('_id'),
                'sprint_id' => $this->sprintList[$i - 1]->getAttribute('_id'),
            ]);

            $task->save();
            $this->taskList[] = $task;
        }
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->profile->delete();
    }

    public function testNotifyProjectMemberAboutUnassignedTasks()
    {
        $cronJob = (new UnassignedTasksReminder(['timer' => '', 'args' => []]))
            ->setApplication($this->getApplication());
        $cronJob->execute();

        $message = '*Reminder*:'
            . 'There are * '
            . 3
            . '* unassigned tasks on active sprints'
            . ', for project *'
            . $this->project->getAttribute('name')
            . '*';

        $slackMessage = $this->getApplication()
            ->getRepositoryManager()
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
        $this->assertEquals($slackMessageAttributes['priority'], SlackService::MEDIUM_PRIORITY);
        $this->assertEquals($slackMessageAttributes['sent'], false);
        $this->assertEquals($slackMessageAttributes['message'], $message);
    }

    public function testDoNotNotifyMemberAboutUnassignedTasksNotMemberOfProject()
    {
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
            ])
            ->save();

        $cronJob = (new UnassignedTasksReminder(['timer' => '', 'args' => []]))
            ->setApplication($this->getApplication());
        $cronJob->execute();

        $slackMessage = $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('slackMessages')
            ->loadOneBy(['recipient' => '@' . $newUser->getAttribute('slack')]);

        $this->assertEquals(null, $slackMessage);
    }
}
