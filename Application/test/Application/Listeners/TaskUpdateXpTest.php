<?php

namespace Application\Test\Application\Listeners;

use Application\Services\ProfilePerformance;
use Application\Test\Application\Traits\Helpers;
use Application\Test\Application\Traits\ProfileRelated;
use Application\Test\Application\Traits\ProjectRelated;
use Framework\Base\Test\UnitTest;
use Application\Listeners\UpdateXp;

class TaskUpdateXpTest extends UnitTest
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

        $this->profile->delete();
    }

    /**
     * test update XP on unfinished task
     */
    public function testTaskUpdateXpUnfinishedTask()
    {
        // Assigned 30 minutes ago
        $minutesWorking = 30;
        $assignedAgo = (int)(new \DateTime())->sub(new \DateInterval('PT' . $minutesWorking . 'M'))->format('U');
        $task = $this->getAssignedTask($assignedAgo);

        $listener = new UpdateXp();
        $listener->setApplication($this->getApplication());
        $out = $listener->handle($task);

        $this->assertEquals(false, $out);
    }

    /**
     * Test task delivery day earlier then due_date
     */
    public function testTaskUpdateXpEarlyTaskDelivery()
    {
        $project = $this->getNewProject();
        $members = $project->getAttribute('members');
        $members[] = $this->profile->getAttribute('_id');
        $project->setAttribute('members', $members);
        $project->save();

        // Assigned 30 minutes ago
        $minutesWorking = 30;
        $assignedAgo = (int)(new \DateTime())->sub(new \DateInterval('PT' . $minutesWorking . 'M'))->format('U');
        $task = $this->getAssignedTask($assignedAgo);

        $task->setAttributes([
            'estimatedHours' => 0.6,
            'complexity' => 5,
            'due_date' => (new \DateTime())->modify('+1 day')->format('U'),
        ])
            ->save();

        $worked = $task->getAttribute('work');
        $passedQaAgo = 5;
        $worked[$task->getAttribute('owner')]['workTrackTimestamp'] =
            (int)(new \DateTime())->sub(new \DateInterval('PT' . $passedQaAgo . 'M'))->format('U');

        $task->setAttributes([
            'submitted_for_qa' => true,
            'work' => $worked,
        ]);
        $task->save();
        $task->setAttribute('passed_qa', true);

        /**
         * @var ProfilePerformance $pp
         */
        $app = $this->getApplication();
        $pp = $app->getService(ProfilePerformance::class);
        $profileValues = $pp->getTaskValuesForProfile($this->profile, $task);
        $profileOldXp = $this->profile->getAttribute('xp');

        $listener = new UpdateXp();
        $listener->setApplication($app);
        $out = $listener->handle($task);

        $checkXpProfile = $app->getRepositoryManager()
            ->getRepositoryFromResourceName('users')
            ->loadOne($this->profile->getAttribute('_id'));

        $this->assertEquals(
            $profileOldXp + $profileValues['xp'],
            $checkXpProfile->getAttribute('xp')
        );
        $this->assertEquals(true, $out);
    }

    /**
     * Test task delivery on due_date
     */
    public function testTaskUpdateXpDeliveredOnTaskDueDate()
    {
        // Assigned 30 minutes ago
        $minutesWorking = 30;
        $assignedAgo = (int)(new \DateTime())->sub(new \DateInterval('PT' . $minutesWorking . 'M'))->format('U');
        $task = $this->getAssignedTask($assignedAgo);

        $task->setAttributes([
            'estimatedHours' => 0.6,
            'complexity' => 5,
            'due_date' => (new \DateTime())->format('U'),
        ])
            ->save();

        $worked = $task->getAttribute('work');
        $passedQaAgo = 5;
        $worked[$task->getAttribute('owner')]['workTrackTimestamp'] =
            (int)(new \DateTime())->sub(new \DateInterval('PT' . $passedQaAgo . 'M'))->format('U');

        $task->setAttributes([
            'submitted_for_qa' => true,
            'work' => $worked,
        ]);
        $task->save();
        $task->setAttribute('passed_qa', true);

        /**
         * @var ProfilePerformance $pp
         */
        $app = $this->getApplication();
        $pp = $app->getService(ProfilePerformance::class);
        $profileValues = $pp->getTaskValuesForProfile($this->profile, $task);
        $profileOldXp = $this->profile->getAttribute('xp');

        $listener = new UpdateXp();
        $listener->setApplication($app);
        $out = $listener->handle($task);

        $checkXpProfile = $app->getRepositoryManager()
            ->getRepositoryFromResourceName('users')
            ->loadOne($this->profile->getAttribute('_id'));

        $this->assertEquals(
            $profileOldXp + $profileValues['xp'],
            $checkXpProfile->getAttribute('xp')
        );
        $this->assertEquals(true, $out);
    }

    /**
     * Test task late delivery
     */
    public function testTaskUpdateXpLateTaskDelivery()
    {
        // Assigned 30 minutes ago
        $minutesWorking = 30;
        $assignedAgo = (int)(new \DateTime())->sub(new \DateInterval('PT' . $minutesWorking . 'M'))->format('U');
        $task = $this->getAssignedTask($assignedAgo);

        $task->setAttributes([
            'estimatedHours' => 0.6,
            'complexity' => 5,
            'due_date' => (new \DateTime())->format('U'),
        ])
            ->save();

        $worked = $task->getAttribute('work');
        $worked[$task->getAttribute('owner')]['workTrackTimestamp'] =
            (int)(new \DateTime())->modify('+2 days')->format('U');

        $task->setAttributes([
            'submitted_for_qa' => true,
            'work' => $worked,
        ]);
        $task->save();
        $task->setAttribute('passed_qa', true);

        /**
         * @var ProfilePerformance $pp
         */
        $app = $this->getApplication();
        $pp = $app->getService(ProfilePerformance::class);
        $profileValues = $pp->getTaskValuesForProfile($this->profile, $task);
        $profileOldXp = $this->profile->getAttribute('xp');

        $listener = new UpdateXp();
        $listener->setApplication($app);
        $out = $listener->handle($task);

        $checkXpProfile = $app->getRepositoryManager()
            ->getRepositoryFromResourceName('users')
            ->loadOne($this->profile->getAttribute('_id'));

        $this->assertEquals(
            $profileOldXp - $profileValues['xpDeduction'],
            $checkXpProfile->getAttribute('xp')
        );
        $this->assertEquals(true, $out);
    }

    /**
     * Test update XP for delivered task in time + QA (project owner)
     */
    public function testTaskUpdateXpProjectOwnerReviewInTime()
    {
        $project = $this->getNewProject();
        $project->setAttribute('acceptedBy', $this->profile->getAttribute('_id'));
        $project->save();

        // Assigned 30 minutes ago
        $minutesWorking = 30;
        $assignedAgo = (int)(new \DateTime())->sub(new \DateInterval('PT' . $minutesWorking . 'M'))->format('U');
        $task = $this->getAssignedTask($assignedAgo);

        $task->setAttributes([
            'estimatedHours' => 0.6,
            'complexity' => 5,
            'due_date' => (new \DateTime())->format('U'),
            'project_id' => $project->getAttribute('_id')
        ])
            ->save();

        $worked = $task->getAttribute('work');

        // Qa was 10 mins
        $taskOwner = $task->getAttribute('owner');
        $worked[$taskOwner]['qa'] = 10 * 60;

        // qa_in_progress was 10 mins
        $worked[$taskOwner]['qa_in_progress'] = 10 * 60;

        // Task worked 15 mins
        $worked[$taskOwner]['worked'] = 15 * 60;

        $passedQaAgo = 5;
        $worked[$taskOwner]['workTrackTimestamp'] =
            (int)(new \DateTime())->sub(new \DateInterval('PT' . $passedQaAgo . 'M'))->format('U');

        $task->setAttribute('work', $worked);
        $task->save();
        $task->setAttribute('passed_qa', true);

        /**
         * @var ProfilePerformance $pp
         */
        $app = $this->getApplication();
        $pp = $app->getService(ProfilePerformance::class);
        $profileValues = $pp->getTaskValuesForProfile($this->profile, $task);
        $profileOldXp = $this->profile->getAttribute('xp');

        $listener = new UpdateXp();
        $listener->setApplication($app);
        $out = $listener->handle($task);

        $checkXpProfile = $app->getRepositoryManager()
            ->getRepositoryFromResourceName('users')
            ->loadOne($this->profile->getAttribute('_id'));

        $this->assertEquals(
            $profileOldXp + $profileValues['xp'] + 0.25,
            $checkXpProfile->getAttribute('xp')
        );
        $this->assertEquals(true, $out);
    }

    /**
     * Check update XP for late review QA - xp deduction
     */
    public function testTaskUpdateXpProjectOwnerReviewLate()
    {
        $project = $this->getNewProject();
        $project->setAttribute('acceptedBy', $this->profile->getAttribute('_id'));
        $project->save();

        // Assigned 30 minutes ago
        $minutesWorking = 30;
        $assignedAgo = (int)(new \DateTime())->sub(new \DateInterval('PT' . $minutesWorking . 'M'))->format('U');
        $task = $this->getAssignedTask($assignedAgo);

        $task->setAttributes([
            'estimatedHours' => 0.6,
            'complexity' => 5,
            'due_date' => (new \DateTime())->format('U'),
            'project_id' => $project->getAttribute('_id')
        ])
            ->save();

        $task->setAttribute('submitted_for_qa', true);

        $worked = $task->getAttribute('work');

        $taskOwner = $task->getAttribute('owner');
        // Qa was 10 mins
        $worked[$taskOwner]['qa'] = 10 * 60;

        // qa_in_progress was 35 mins
        $worked[$taskOwner]['qa_in_progress'] = 35 * 60;

        // Task worked 10 mins
        $worked[$taskOwner]['worked'] = 10 * 60;

        $passedQaAgo = 5;
        $worked[$taskOwner]['workTrackTimestamp'] =
            (int)(new \DateTime())->sub(new \DateInterval('PT' . $passedQaAgo . 'M'))->format('U');

        $task->setAttribute('work', $worked);
        $task->save();
        $task->setAttribute('passed_qa', true);

        /**
         * @var ProfilePerformance $pp
         */
        $app = $this->getApplication();
        $pp = $app->getService(ProfilePerformance::class);
        $profileValues = $pp->getTaskValuesForProfile($this->profile, $task);
        $profileOldXp = $this->profile->getAttribute('xp');

        $listener = new UpdateXp();
        $listener->setApplication($app);
        $out = $listener->handle($task);

        $checkXpProfile = $app->getRepositoryManager()
            ->getRepositoryFromResourceName('users')
            ->loadOne($this->profile->getAttribute('_id'));

        // Task owner get's XP for early delivery and xp is deducted because code not reviewed in time
        $this->assertEquals(
            $profileOldXp + $profileValues['xp'] - 3,
            $checkXpProfile->getAttribute('xp')
        );
        $this->assertEquals(true, $out);
    }

    /**
     * Test task update XP with low priority task without any high or medium priority unassigned tasks and with
     * other low priority task
     */
    public function testTaskUpdateXpTaskPriorityOnlyLow()
    {
        $this->markTestSkipped(
            'Mark as uncompleted. Need truncate method on repository to clear database records.'
        );
        $project = $this->getNewProject();
        $members = $project->getAttribute('members');
        $members[] = $this->profile->getAttribute('_id');
        $project->setAttributes([
            'members' => $members,
            'acceptedBy' => $this->profile->getAttribute('_id')
        ]);
        $project->save();

        $skillSet = ['PHP'];
        $taskLowPriorityWithoutOwner = $this->getNewTask();
        $taskLowPriorityWithoutOwner->setAttributes([
            'project_id' => $project->getAttribute('_id'),
            'priority' => 'Low',
            'skillset' => $skillSet
        ])
            ->save();

        // Assigned 30 minutes ago
        $minutesWorking = 30;
        $assignedAgo = (int)(new \DateTime())->sub(new \DateInterval('PT' . $minutesWorking . 'M'))->format('U');

        $taskLowPriority = $this->getAssignedTask($assignedAgo);
        $taskLowPriority->setAttributes([
            'project_id' => $project->getAttribute('_id'),
            'priority' => 'Low',
            'skillset' => $skillSet,
            'due_date' => (new \DateTime())->modify('+1 day')->format('U'),
            'complexity' => 5,
            'estimatedHours' => 0.6
        ])
            ->save();

        $taskLowPriority->setAttribute('submitted_for_qa', true);
        $worked = $taskLowPriority->getAttribute('work');

        $taskLowPriorityOwner = $taskLowPriority->getAttribute('owner');
        // Qa was 10 mins
        $worked[$taskLowPriorityOwner]['qa'] = 10 * 60;

        // Task worked 15 mins
        $worked[$taskLowPriorityOwner]['worked'] = 15 * 60;

        $passedQaAgo = 5;
        $worked[$taskLowPriorityOwner]['workTrackTimestamp'] =
            (int)(new \DateTime())->sub(new \DateInterval('PT' . $passedQaAgo . 'M'))->format('U');

        $taskLowPriority->setAttribute('work', $worked);
        $taskLowPriority->save();
        $taskLowPriority->setAttribute('passed_qa', true);

        /**
         * @var ProfilePerformance $pp
         */
        $app = $this->getApplication();
        $pp = $app->getService(ProfilePerformance::class);
        $profileValues = $pp->getTaskValuesForProfile($this->profile, $taskLowPriority);
        $profileOldXp = $this->profile->getAttribute('xp');

        $listener = new UpdateXp();
        $listener->setApplication($app);
        $out = $listener->handle($taskLowPriority);

        $checkXpProfile = $app->getRepositoryManager()
            ->getRepositoryFromResourceName('users')
            ->loadOne($this->profile->getAttribute('_id'));

        // Task owner get's XP for early delivery and xp is deducted because code not reviewed in time
        $this->assertEquals(
            $profileOldXp + $profileValues['xp'],
            $checkXpProfile->getAttribute('xp')
        );
        $this->assertEquals(true, $out);
    }

    /**
     * Test task deduct Xp award for low priority task because there are medium and high priority unassigned tasks
     */
    public function testTaskUpdateXpTaskPriorityLowDeduct()
    {
        $this->markTestSkipped(
            'Mark as uncompleted. Need truncate method on repository to clear database records.'
        );

        $project = $this->getNewProject();
        $members = $project->getAttribute('members');
        $members[] = $this->profile->getAttribute('_id');
        $project->setAttributes([
            'members' => $members,
            'acceptedBy' => $this->profile->getAttribute('_id')
        ]);
        $project->save();

        $skillSet = [
            'PHP',
            'React',
            'DevOps',
        ];

        $taskMediumPriorityWithoutOwner = $this->getNewTask();
        $taskMediumPriorityWithoutOwner->setAttributes([
            'project_id' => $project->getAttribute('_id'),
            'priority' => 'Medium',
            'skillset' => $skillSet
        ])
            ->save();

        $taskHighPriorityWithoutOwner = $this->getNewTask();
        $taskHighPriorityWithoutOwner->setAttributes([
            'project_id' => $project->getAttribute('_id'),
            'priority' => 'High',
            'skillset' => $skillSet
        ])
            ->save();

        // Assigned 30 minutes ago
        $minutesWorking = 30;
        $assignedAgo = (int)(new \DateTime())->sub(new \DateInterval('PT' . $minutesWorking . 'M'))->format('U');

        $taskLowPriority = $this->getAssignedTask($assignedAgo);
        $taskLowPriority->setAttributes([
            'project_id' => $project->getAttribute('_id'),
            'priority' => 'Medium',
            'skillset' => $skillSet,
            'due_date' => (new \DateTime())->modify('+1 day')->format('U'),
            'complexity' => 5,
            'estimatedHours' => 0.6
        ])
            ->save();

        $taskLowPriority->setAttribute('submitted_for_qa', true);
        $worked = $taskLowPriority->getAttribute('work');

        $taskLowPriorityOwner = $taskLowPriority->getAttribute('owner');
        // Qa was 10 mins
        $worked[$taskLowPriorityOwner]['qa'] = 10 * 60;

        // Task worked 15 mins
        $worked[$taskLowPriorityOwner]['worked'] = 15 * 60;

        $passedQaAgo = 5;
        $worked[$taskLowPriorityOwner]['workTrackTimestamp'] =
            (int)(new \DateTime())->sub(new \DateInterval('PT' . $passedQaAgo . 'M'))->format('U');

        $taskLowPriority->setAttribute('work', $worked);
        $taskLowPriority->save();
        $taskLowPriority->setAttribute('passed_qa', true);

        /**
         * @var ProfilePerformance $pp
         */
        $app = $this->getApplication();
        $pp = $app->getService(ProfilePerformance::class);
        $profileValues = $pp->getTaskValuesForProfile($this->profile, $taskLowPriority);
        $profileOldXp = $this->profile->getAttribute('xp');

        $listener = new UpdateXp();
        $listener->setApplication($app);
        $out = $listener->handle($taskLowPriority);

        $checkXpProfile = $app->getRepositoryManager()
            ->getRepositoryFromResourceName('users')
            ->loadOne($this->profile->getAttribute('_id'));

        // Task owner get's XP for early delivery and xp is deducted because code not reviewed in time
        $this->assertEquals(
            $profileOldXp + $profileValues['xp'],
            $checkXpProfile->getAttribute('xp')
        );
        $this->assertEquals(true, $out);
    }

    /**
     * Test task update XP with medium priority task, without any unassigned high priority and with unassigned low
     * priority task
     */
    public function testTaskUpdateXpTaskPriorityMediumOrLow()
    {
        $this->markTestSkipped(
            'Mark as uncompleted. Need truncate method on repository to clear database records.'
        );

        $project = $this->getNewProject();
        $members = $project->getAttribute('members');
        $members[] = $this->profile->getAttribute('_id');
        $project->setAttributes([
            'members' => $members,
            'acceptedBy' => $this->profile->getAttribute('_id')
        ]);
        $project->save();

        $skillSet = [
            'PHP',
            'React',
            'DevOps',
        ];

        $taskLowPriorityWithoutOwner = $this->getNewTask();
        $taskLowPriorityWithoutOwner->setAttributes([
            'project_id' => $project->getAttribute('_id'),
            'priority' => 'Medium',
            'skillset' => $skillSet
        ])
            ->save();

        // Assigned 30 minutes ago
        $minutesWorking = 30;
        $assignedAgo = (int)(new \DateTime())->sub(new \DateInterval('PT' . $minutesWorking . 'M'))->format('U');

        $taskMediumPriority = $this->getAssignedTask($assignedAgo);
        $taskMediumPriority->setAttributes([
            'project_id' => $project->getAttribute('_id'),
            'priority' => 'Medium',
            'skillset' => $skillSet,
            'due_date' => (new \DateTime())->modify('+1 day')->format('U'),
            'complexity' => 5,
            'estimatedHours' => 0.6
        ])
            ->save();

        $taskMediumPriority->setAttribute('submitted_for_qa', true);
        $worked = $taskMediumPriority->getAttribute('work');

        $taskMediumPriorityOwner = $taskMediumPriority->getAttribute('owner');
        // Qa was 10 mins
        $worked[$taskMediumPriorityOwner]['qa'] = 10 * 60;

        // Task worked 15 mins
        $worked[$taskMediumPriorityOwner]['worked'] = 15 * 60;

        $passedQaAgo = 5;
        $worked[$taskMediumPriorityOwner]['workTrackTimestamp'] =
            (int)(new \DateTime())->sub(new \DateInterval('PT' . $passedQaAgo . 'M'))->format('U');

        $taskMediumPriority->setAttribute('work', $worked);
        $taskMediumPriority->save();
        $taskMediumPriority->setAttribute('passed_qa', true);

        /**
         * @var ProfilePerformance $pp
         */
        $app = $this->getApplication();
        $pp = $app->getService(ProfilePerformance::class);
        $profileValues = $pp->getTaskValuesForProfile($this->profile, $taskMediumPriority);
        $profileOldXp = $this->profile->getAttribute('xp');

        $listener = new UpdateXp();
        $listener->setApplication($app);
        $out = $listener->handle($taskMediumPriority);

        $checkXpProfile = $app->getRepositoryManager()
            ->getRepositoryFromResourceName('users')
            ->loadOne($this->profile->getAttribute('_id'));

        // Task owner get's XP for early delivery and xp is deducted because code not reviewed in time
        $this->assertEquals(
            $profileOldXp + $profileValues['xp'],
            $checkXpProfile->getAttribute('xp')
        );
        $this->assertEquals(true, $out);
    }

    /**
     * Test task deduct XP award for medium priority task because there is unassigned high priority task
     */
    public function testTaskUpdateXpTaskPriorityMediumDeduct()
    {
        $this->markTestSkipped(
            'Mark as uncompleted. Need truncate method on repository to clear database records.'
        );

        $project = $this->getNewProject();
        $members = $project->getAttribute('members');
        $members[] = $this->profile->getAttribute('_id');
        $project->setAttributes([
            'members' => $members,
            'acceptedBy' => $this->profile->getAttribute('_id')
        ]);
        $project->save();

        $skillSet = [
            'PHP',
            'React',
            'DevOps',
        ];

        $taskHighPriorityWithoutOwner = $this->getNewTask();
        $taskHighPriorityWithoutOwner->setAttributes([
            'project_id' => $project->getAttribute('_id'),
            'priority' => 'Medium',
            'skillset' => $skillSet
        ])
            ->save();

        $taskMediumPriorityWithoutOwner = $this->getNewTask();
        $taskMediumPriorityWithoutOwner->setAttributes([
            'project_id' => $project->getAttribute('_id'),
            'priority' => 'Medium',
            'skillset' => $skillSet
        ])
            ->save();

        // Assigned 30 minutes ago
        $minutesWorking = 30;
        $assignedAgo = (int)(new \DateTime())->sub(new \DateInterval('PT' . $minutesWorking . 'M'))->format('U');

        $taskMediumPriority = $this->getAssignedTask($assignedAgo);
        $taskMediumPriority->setAttributes([
            'project_id' => $project->getAttribute('_id'),
            'priority' => 'Medium',
            'skillset' => $skillSet,
            'due_date' => (new \DateTime())->modify('+1 day')->format('U'),
            'complexity' => 5,
            'estimatedHours' => 0.6
        ])
            ->save();

        $taskMediumPriority->setAttribute('submitted_for_qa', true);
        $worked = $taskMediumPriority->getAttribute('work');

        $taskMediumPriorityOwner = $taskMediumPriority->getAttribute('owner');
        // Qa was 10 mins
        $worked[$taskMediumPriorityOwner]['qa'] = 10 * 60;

        // Task worked 15 mins
        $worked[$taskMediumPriorityOwner]['worked'] = 15 * 60;

        $passedQaAgo = 5;
        $worked[$taskMediumPriorityOwner]['workTrackTimestamp'] =
            (int)(new \DateTime())->sub(new \DateInterval('PT' . $passedQaAgo . 'M'))->format('U');

        $taskMediumPriority->setAttribute('work', $worked);
        $taskMediumPriority->save();
        $taskMediumPriority->setAttribute('passed_qa', true);

        /**
         * @var ProfilePerformance $pp
         */
        $app = $this->getApplication();
        $pp = $app->getService(ProfilePerformance::class);
        $profileValues = $pp->getTaskValuesForProfile($this->profile, $taskMediumPriority);
        $profileOldXp = $this->profile->getAttribute('xp');

        $listener = new UpdateXp();
        $listener->setApplication($app);
        $out = $listener->handle($taskMediumPriority);

        $checkXpProfile = $app->getRepositoryManager()
            ->getRepositoryFromResourceName('users')
            ->loadOne($this->profile->getAttribute('_id'));

        // Task owner get's XP for early delivery and xp is deducted because code not reviewed in time
        $this->assertEquals(
            $profileOldXp + $profileValues['xp'],
            $checkXpProfile->getAttribute('xp')
        );
        $this->assertEquals(true, $out);
    }

    /**
     * Test task update XP with high priority task
     */
    public function testTaskUpdateXpTaskPriorityHigh()
    {
        $this->markTestSkipped(
            'Mark as uncompleted. Need truncate method on repository to clear database records.'
        );

        $project = $this->getNewProject();
        $members = $project->getAttribute('members');
        $members[] = $this->profile->getAttribute('_id');
        $project->setAttributes([
            'members' => $members,
            'acceptedBy' => $this->profile->getAttribute('_id')
        ]);
        $project->save();

        $skillSet = [
            'PHP',
            'React',
            'DevOps',
        ];

        $taskHighPriorityWithoutOwner = $this->getNewTask();
        $taskHighPriorityWithoutOwner->setAttributes([
            'project_id' => $project->getAttribute('_id'),
            'priority' => 'Medium',
            'skillset' => $skillSet
        ])
            ->save();

        $taskMediumPriorityWithoutOwner = $this->getNewTask();
        $taskMediumPriorityWithoutOwner->setAttributes([
            'project_id' => $project->getAttribute('_id'),
            'priority' => 'Medium',
            'skillset' => $skillSet
        ])
            ->save();

        // Assigned 30 minutes ago
        $minutesWorking = 30;
        $assignedAgo = (int)(new \DateTime())->sub(new \DateInterval('PT' . $minutesWorking . 'M'))->format('U');

        $taskHighPriority = $this->getAssignedTask($assignedAgo);
        $taskHighPriority->setAttributes([
            'project_id' => $project->getAttribute('_id'),
            'priority' => 'High',
            'skillset' => $skillSet,
            'due_date' => (new \DateTime())->modify('+1 day')->format('U'),
            'complexity' => 5,
            'estimatedHours' => 0.6
        ])
            ->save();

        $taskHighPriority->setAttribute('submitted_for_qa', true);
        $worked = $taskHighPriority->getAttribute('work');

        $taskMediumPriorityOwner = $taskHighPriority->getAttribute('owner');
        // Qa was 10 mins
        $worked[$taskMediumPriorityOwner]['qa'] = 10 * 60;

        // Task worked 15 mins
        $worked[$taskMediumPriorityOwner]['worked'] = 15 * 60;

        $passedQaAgo = 5;
        $worked[$taskMediumPriorityOwner]['workTrackTimestamp'] =
            (int)(new \DateTime())->sub(new \DateInterval('PT' . $passedQaAgo . 'M'))->format('U');

        $taskHighPriority->setAttribute('work', $worked);
        $taskHighPriority->save();
        $taskHighPriority->setAttribute('passed_qa', true);

        /**
         * @var ProfilePerformance $pp
         */
        $app = $this->getApplication();
        $pp = $app->getService(ProfilePerformance::class);
        $profileValues = $pp->getTaskValuesForProfile($this->profile, $taskHighPriority);
        $profileOldXp = $this->profile->getAttribute('xp');

        $listener = new UpdateXp();
        $listener->setApplication($app);
        $out = $listener->handle($taskHighPriority);

        $checkXpProfile = $app->getRepositoryManager()
            ->getRepositoryFromResourceName('users')
            ->loadOne($this->profile->getAttribute('_id'));

        // Task owner get's XP for early delivery and xp is deducted because code not reviewed in time
        $this->assertEquals(
            $profileOldXp + $profileValues['xp'],
            $checkXpProfile->getAttribute('xp')
        );
        $this->assertEquals(true, $out);
    }

    /**
     * Test listener for task that has been updated after task passed QA already
     */
    public function testTaskUpdateXpForTaskUpdatedAfterPassedQa()
    {
        $task = $this->getAssignedTask();
        $task->setAttribute('passed_qa', true);
        $task->save();

        // Test finished task without any change
        $listener = new UpdateXp();
        $listener->setApplication($this->getApplication());
        $out = $listener->handle($task);

        $this->assertEquals(false, $out);

        // Let's make some update
        $task->setAttributes([
            'priority' => 'High',
            'title' => 'Test'
        ]);

        // Test finished task with some updates
        $out = $listener->handle($task);
        $this->assertEquals(false, $out);

        $task->save();
        $task->setAttribute('passed_qa', false);

        // Test finished task with update passed_qa = false
        $out = $listener->handle($task);

        $this->assertEquals(false, $out);
    }

    /**
     * Test xp deduction if profilePerformance mapped values for xpDeduction is lower than 1
     * (should deduct at least 1 xp)
     */
    public function testTaskUpdateXpDeductionAtLeastOneXp()
    {
        $this->profile->setAttribute('xp', 1000);
        $this->profile->save();

        $task = $this->getAssignedTask();
        $task->setAttributes([
            'due_date' => (new \DateTime())->sub(new \DateInterval('P1D'))->format('U'),
            'estimatedHours' => 0.1
        ])
            ->save();

        $profileOldXp = $this->profile->getAttribute('xp');

        $task->setAttribute('passed_qa', true);
        $listener = new UpdateXp();
        $listener->setApplication($this->getApplication());
        $out = $listener->handle($task);

        $checkXpProfile = $this->getApplication()->getRepositoryManager()
            ->getRepositoryFromResourceName('users')
            ->loadOne($this->profile->getAttribute('_id'));

        // Task owner get's XP for early delivery and xp is deducted because code not reviewed in time
        $this->assertEquals(
            $profileOldXp - 1,
            $checkXpProfile->getAttribute('xp')
        );
        $this->assertEquals(true, $out);
    }
}
