<?php

namespace Application\Test\Application\CronJobs;

use Application\CronJobs\TaskDeadlineReminder;
use Application\Services\SlackService;
use Application\Test\Application\Traits\Helpers;
use Application\Test\Application\Traits\ProfileRelated;
use Application\Test\Application\Traits\ProjectRelated;
use Framework\Base\Model\BrunoInterface;
use Framework\Base\Test\UnitTest;

class TaskDeadlineReminderTest extends UnitTest
{
    use ProjectRelated, ProfileRelated, Helpers;

    const DUE_DATE_PASSED = 'due_date_passed';
    const DUE_DATE_SOON = 'due_date_soon';

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
            ])
            ->save();

        $this->setTaskOwner($newUser);

        $project = $this->getNewProject();
        $members = [$this->profile->getAttribute('_id')];
        $project->setAttribute('members', $members);
        $project->save();
        $this->project = $project;

        $sprint = $this->getNewSprint();
        $sprint->save();

        for ($i = 1; $i < 6; $i++) {
            $task = $this->getNewTask();
            $task->setAttributes([
                'ready' => true,
                'due_date' => (int)(new \DateTime())->modify("+{$i} days")->format('U'),
                'skillset' => ['PHP', 'React'],
                'project_id' => $this->project->getAttribute('_id'),
                'sprint_id' => $sprint->getAttribute('_id'),
            ]);
            $task->save();
            $this->taskList[] = $task;
        }

        $taskDeadlinePassed = $this->getNewTask();
        $taskDeadlinePassed->setAttributes([
            'ready' => true,
            'due_date' => (int)(new \DateTime())->modify("-1 days")->format('U'),
            'skillset' => ['PHP', 'React'],
            'project_id' => $this->project->getAttribute('_id'),
            'sprint_id' => $sprint->getAttribute('_id'),
        ]);
        $taskDeadlinePassed->save();
        $this->taskList[] = $taskDeadlinePassed;
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->profile->delete();

        foreach ($this->taskList as $task) {
            $task->delete();
        }

        $this->project->delete();
    }

    /**
     * test notify user on slack about task deadlines - success
     */
    public function testNotifyProjectMemberForTaskDeadlines()
    {
        $cronJob = new TaskDeadlineReminder(['timer' => '', 'args' => []]);
        $cronJob->setApplication($this->getApplication());
        $cronJob->execute();

        $slackMessage = $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('slackMessages')
            ->loadOneBy(['recipient' => '@' . $this->profile->getAttribute('slack')]);

        $slackMessageAttributes = $slackMessage->getAttributes();

        $tasks = [
            $this->taskList[0],
            $this->taskList[1],
            $this->taskList[2],
        ];

        $message = $this->createMessage(self::DUE_DATE_SOON, $tasks);

        $this->assertArrayHasKey('recipient', $slackMessageAttributes);
        $this->assertArrayHasKey('message', $slackMessageAttributes);
        $this->assertArrayHasKey('priority', $slackMessageAttributes);
        $this->assertArrayHasKey('sent', $slackMessageAttributes);
        $this->assertArrayHasKey('runAt', $slackMessageAttributes);
        $this->assertEquals(
            $slackMessageAttributes['recipient'],
            '@' . $this->profile->getAttribute('slack')
        );
        $this->assertEquals($slackMessageAttributes['priority'], SlackService::LOW_PRIORITY);
        $this->assertEquals($slackMessageAttributes['sent'], false);
        $this->assertEquals($slackMessageAttributes['message'], $message);
        $slackMessage->delete();
    }

    public function testDoNotNotifyMemberForTaskDeadlineNotAMemberOfProject()
    {
        $user = $this->getApplication()->getRepositoryManager()
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
                'admin' => false
            ])
            ->save();

        $members = [];
        $project = $this->getNewProject();
        $project->setAttribute('members', $members);
        $project->save();
        $this->project = $project;

        foreach ($this->taskList as $task) {
            $task->setAttribute('project_id', $this->project->getAttribute('_id'));
            $task->save();
        }

        $cronJob = new TaskDeadlineReminder(['timer' => '', 'args' => []]);
        $cronJob->setApplication($this->getApplication());
        $cronJob->execute();

        $slackMessage = $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('slackMessages')
            ->loadOneBy(['recipient' => '@' . $user->getAttribute('slack')]);

        $this->assertEquals(null, $slackMessage);
    }

    public function testNotifyPoAboutDeadlineMissedAndDeadlineSoonTasks()
    {
        $projectOwner = $this->getApplication()->getRepositoryManager()
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
                'admin' => false
            ])
            ->save();

        $members = [];
        $project = $this->getNewProject();
        $project->setAttribute('members', $members);
        $project->setAttribute('acceptedBy', $projectOwner->getAttribute('_id'));
        $project->save();
        $this->project = $project;

        foreach ($this->taskList as $task) {
            $task->setAttribute('project_id', $this->project->getAttribute('_id'));
            $task->save();
        }

        $cronJob = new TaskDeadlineReminder(['timer' => '', 'args' => []]);
        $cronJob->setApplication($this->getApplication());
        $cronJob->execute();

        $slackMessages = $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('slackMessages')
            ->loadMultiple(['recipient' => '@' . $projectOwner->getAttribute('slack')]);

        $slackMessages = array_values($slackMessages);

        $tasksDueDateSoon = [
            $this->taskList[0],
            $this->taskList[1],
            $this->taskList[2],
        ];

        $taskPassed = end($this->taskList);

        $messageSoonDueDate = $this->createMessage(self::DUE_DATE_SOON, $tasksDueDateSoon);
        $messageDueDatePassed = $this->createMessage(self::DUE_DATE_PASSED, [$taskPassed]);

        $this->assertEquals(2, count($slackMessages));

        $dueDateDueDateSoonMessageAtt = $slackMessages[0]->getAttributes();
        $dueDateDueDatePassedMessageAtt = $slackMessages[1]->getAttributes();


        $this->assertArrayHasKey('recipient', $dueDateDueDateSoonMessageAtt);
        $this->assertArrayHasKey('message', $dueDateDueDateSoonMessageAtt);
        $this->assertArrayHasKey('priority', $dueDateDueDateSoonMessageAtt);
        $this->assertArrayHasKey('sent', $dueDateDueDateSoonMessageAtt);
        $this->assertArrayHasKey('runAt', $dueDateDueDateSoonMessageAtt);
        $this->assertEquals(
            $dueDateDueDateSoonMessageAtt['recipient'],
            '@' . $projectOwner->getAttribute('slack')
        );
        $this->assertEquals($dueDateDueDateSoonMessageAtt['priority'], SlackService::LOW_PRIORITY);
        $this->assertEquals($dueDateDueDateSoonMessageAtt['sent'], false);
        $this->assertEquals($dueDateDueDateSoonMessageAtt['message'], $messageSoonDueDate);

        $this->assertArrayHasKey('recipient', $dueDateDueDatePassedMessageAtt);
        $this->assertArrayHasKey('message', $dueDateDueDatePassedMessageAtt);
        $this->assertArrayHasKey('priority', $dueDateDueDatePassedMessageAtt);
        $this->assertArrayHasKey('sent', $dueDateDueDatePassedMessageAtt);
        $this->assertArrayHasKey('runAt', $dueDateDueDatePassedMessageAtt);
        $this->assertEquals(
            $dueDateDueDatePassedMessageAtt['recipient'],
            '@' . $projectOwner->getAttribute('slack')
        );
        $this->assertEquals($dueDateDueDatePassedMessageAtt['priority'], SlackService::LOW_PRIORITY);
        $this->assertEquals($dueDateDueDatePassedMessageAtt['sent'], false);
        $this->assertEquals($dueDateDueDatePassedMessageAtt['message'], $messageDueDatePassed);

        foreach ($slackMessages as $message) {
            $message->delete();
        }
    }
}
