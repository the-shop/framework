<?php

namespace Application\Test\Services;

use Application\Helpers\WorkDays;
use Application\Services\ProfilePerformance;
use Application\Test\Application\Traits\Helpers;
use Framework\Base\Test\UnitTest;
use Application\Test\Application\Traits\ProfileRelated;
use Application\Test\Application\Traits\ProjectRelated;

class ProfilePerformanceTest extends UnitTest
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
     * Test empty task history
     */
    public function testCheckPerformanceForEmptyHistory()
    {
        $task = $this->getAssignedTask();

        $pp = $this->getApplication()->getService(ProfilePerformance::class);

        $out = $pp->perTask($task);

        $taskWorkTrack =
            $task->getAttribute('work')[$this->profile->getAttribute('_id')]['workTrackTimestamp'];

        $this->assertEquals(
            [
                $this->profile->getAttribute('_id') => [
                    'workSeconds' => 0,
                    'pauseSeconds' => 0,
                    'qaSeconds' => 0,
                    'qaProgressSeconds' => 0,
                    'qaProgressTotalSeconds' => 0,
                    'totalNumberFailedQa' => 0,
                    'blockedSeconds' => 0,
                    'workTrackTimestamp' => $taskWorkTrack,
                    'taskLastOwner' => true,
                    'taskCompleted' => false,
                ],
            ],
            $out
        );
    }

    /**
     * Test task just got assigned
     */
    public function testCheckPerformanceForTaskAssigned()
    {
        // Assigned 5 minutes ago
        $minutesWorking = 5;
        $assignedAgo = (int)(new \DateTime())->sub(new \DateInterval('PT' . $minutesWorking . 'M'))->format('U');
        $task = $this->getTaskWithJustAssignedHistory($assignedAgo);

        $pp = $this->getApplication()->getService(ProfilePerformance::class);

        $out = $pp->perTask($task);

        $this->assertCount(1, $out);

        $this->assertArrayHasKey($this->profile->getAttribute('_id'), $out);

        $profilePerformanceArray = $out[$this->profile->getAttribute('_id')];

        $this->assertArrayHasKey('taskCompleted', $profilePerformanceArray);
        $this->assertArrayHasKey('workSeconds', $profilePerformanceArray);
        $this->assertArrayHasKey('pauseSeconds', $profilePerformanceArray);
        $this->assertArrayHasKey('qaSeconds', $profilePerformanceArray);
        $this->assertArrayHasKey('qaProgressSeconds', $profilePerformanceArray);
        $this->assertArrayHasKey('qaProgressTotalSeconds', $profilePerformanceArray);
        $this->assertArrayHasKey('blockedSeconds', $profilePerformanceArray);
        $this->assertArrayHasKey('workTrackTimestamp', $profilePerformanceArray);


        $this->assertEquals(false, $profilePerformanceArray['taskCompleted']);
        $this->assertEquals($minutesWorking * 60, $profilePerformanceArray['workSeconds']);
        $this->assertEquals(0, $profilePerformanceArray['qaSeconds']);
        $this->assertEquals(0, $profilePerformanceArray['pauseSeconds']);
    }

    /**
     * Test profile performance XP difference output for 5 days with XP record
     */
    public function testProfilePerformanceForTimeRangeXpDiff()
    {
        $profileXpRecord = $this->getXpRecord();
        $workDays = WorkDays::getWorkDays();
        foreach ($workDays as $day) {
            $this->addXpRecord(
                $profileXpRecord,
                \DateTime::createFromFormat('Y-m-d', $day)->format('U')
            );
        }

        $pp = $this->getApplication()->getService(ProfilePerformance::class);
        //Test XP diff within time range with XP records
        $out = $pp->aggregateForTimeRange(
            $this->profile,
            (int)\DateTime::createFromFormat('Y-m-d', $workDays[0])->format('U'),
            (int)\DateTime::createFromFormat('Y-m-d', $workDays[4])->format('U')
        );

        $this->assertEquals(5, $out['xpDiff']);
    }

    /**
     * Test Test profile performance XP difference output for 10 days with XP record
     */
    public function testProfilePerformanceForTimeRangeXpDifference()
    {
        $profileXpRecord = $this->getXpRecord();
        $workDays = WorkDays::getWorkDays();
        foreach ($workDays as $day) {
            $this->addXpRecord(
                $profileXpRecord,
                \DateTime::createFromFormat('Y-m-d', $day)->format('U')
            );
        }

        $pp = $this->getApplication()->getService(ProfilePerformance::class);
        //Test XP diff within time range with XP records
        $out = $pp->aggregateForTimeRange(
            $this->profile,
            (int)\DateTime::createFromFormat('Y-m-d', $workDays[6])->format('U'),
            (int)\DateTime::createFromFormat('Y-m-d', $workDays[15])->format('U')
        );

        $this->assertEquals(10, $out['xpDiff']);
    }

    /**
     * Test Test profile performance XP difference for time range where there are no XP records
     */
    public function testProfilePerformanceForTimeRangeXpDifferenceWithNoXp()
    {
        $profileXpRecord = $this->getXpRecord();
        $workDays = WorkDays::getWorkDays();
        foreach ($workDays as $day) {
            $this->addXpRecord(
                $profileXpRecord,
                \DateTime::createFromFormat('Y-m-d', $day)->format('U')
            );
        }

        $pp = $this->getApplication()->getService(ProfilePerformance::class);
        //Test XP diff for time range where there are no XP records
        $startTime = (int)(new \DateTime())->modify('+50 days')->format('U');
        $endTime = (int)(new \DateTime())->modify('+55 days')->format('U');
        $out = $pp->aggregateForTimeRange($this->profile, $startTime, $endTime);

        $this->assertEquals(0, $out['xpDiff']);
    }

    /**
     * Test Test profile performance XP difference for time range of 3 days (2 days are without XP records)
     */
    public function testProfilePerformanceForTimeRangeFiveDaysXpDifference()
    {
        $profileXpRecord = $this->getXpRecord();
        $workDays = WorkDays::getWorkDays();
        foreach ($workDays as $day) {
            $this->addXpRecord(
                $profileXpRecord,
                \DateTime::createFromFormat('Y-m-d', $day)->format('U')
            );
        }

        $pp = $this->getApplication()->getService(ProfilePerformance::class);
        //Test XP diff when first 2 days of check there are no xp records and 3rd day there is one record
        $twoDaysBeforeFirstWorkDay = (int)(new \DateTime(reset($workDays)))->modify('-2 days')->format('U');
        $firstWorkDay = (int)\DateTime::createFromFormat('Y-m-d', reset($workDays))->format('U');

        $out = $pp->aggregateForTimeRange(
            $this->profile,
            $twoDaysBeforeFirstWorkDay,
            $firstWorkDay
        );

        $this->assertEquals(1, $out['xpDiff']);
    }

    /**
     * Test profile performance for six days time range, with some tasks within time range and out of time range
     */
    public function testProfilePerformanceTaskCalculationDeliveryForTimeRangeSixDays()
    {
        $project = $this->getNewProject();
        $project->save();

        $workDays = WorkDays::getWorkDays();
        $tasks = [];
        $counter = 1;
        foreach ($workDays as $day) {
            $unixDay = \DateTime::createFromFormat('Y-m-d', $day)->format('U');
            $task = $this->getAssignedTask($unixDay);
            $task->setAttributes([
                'estimatedHours' => 1,
                'project_id' => $project->getAttribute('_id'),
            ]);
            if ($counter % 2 === 0) {
                $work = $task->getAttribute('work');
                $work[$this->profile->getAttribute('_id')]['qa_total_time'] = 1800;
                $task->setAttributes(
                    [
                        'passed_qa' => true,
                        'timeFinished' => (int)$unixDay,
                        'work' => $work,
                    ]
                );
            }
            $task->save();
            $tasks[$unixDay] = $task;
            $counter++;
        }

        $workDaysUnixTimestamps = array_keys($tasks);

        $pp = $this->getApplication()->getService(ProfilePerformance::class);
        $out = $pp->aggregateForTimeRange(
            $this->profile,
            $workDaysUnixTimestamps[0],
            $workDaysUnixTimestamps[5]
        );

        $this->assertEquals(30, $out['estimatedHours']);
        $this->assertEquals(15, $out['hoursDelivered']);
        $this->assertEquals(3000, $out['totalPayoutExternal']);
        $this->assertEquals(1500, $out['realPayoutExternal']);
        $this->assertEquals(0, $out['totalPayoutInternal']);
        $this->assertEquals(0, $out['totalPayoutInternal']);
        $this->assertEquals(0, $out['realPayoutInternal']);
        $this->assertEquals(1.5, $out['hoursDoingQA']);
    }

    /**
     * Test profile performance aggregate for time range wrong input format
     */
    public function testProfilePerformanceAggregateForTimeRangeWrongInput()
    {
        // String timestamp
        $unixNow = (new \DateTime())->format('U');
        // Integer timestamp
        $unix2DaysAgo =
            (int)(new \DateTime())
                ->sub(new \DateInterval('P2D'))
                ->format('U');

        $pp = $this->getApplication()->getService(ProfilePerformance::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid time range input. Must be type of integer');
        $this->expectExceptionCode(403);

        $out = $pp->aggregateForTimeRange($this->profile, $unix2DaysAgo, $unixNow);
        $this->assertEquals($out, $this->getExpectedException());

        // Integer timestamp
        $unixNowInteger = (int)(new \DateTime())->format('U');
        // String timestamp
        $unix2DaysAgoString =
            (new \DateTime())
                ->sub(new \DateInterval('P2D'))
                ->format('U');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid time range input. Must be type of integer');
        $this->expectExceptionCode(403);

        $out = $pp->aggregateForTimeRange($this->profile, $unix2DaysAgoString, $unixNowInteger);
        $this->assertEquals($out, $this->getExpectedException());
    }

    /**
     * Test profile performance task priority coefficient = 1.0
     */
    public function testProfilePerformanceTaskPriorityCoefficientNoDeduction()
    {
        // Get new project
        $project = $this->getNewProject();
        $members = [$this->profile->getAttribute('_id')];
        $project->setAttribute('members', $members);
        $project->save();

        // Create skillsets for tasks
        $skillSetMatch = [
            'PHP',
            'Planning',
            'React',
        ];

        // Create some tasks and set skillset
        $lowPriorityTask = $this->getNewTask();
        $lowPriorityTask->setAttributes([
            'project_id' => $project->getAttribute('_id'),
            'priority' => 'low',
            'skillset' => $skillSetMatch
        ]);
        $lowPriorityTask->save();

        $mediumPriorityTask = $this->getNewTask();
        $mediumPriorityTask->setAttributes([
            'project_id' => $project->getAttribute('_id'),
            'priority' => 'medium',
            'skillset' => $skillSetMatch
        ]);
        $mediumPriorityTask->save();

        $highPriorityTask = $this->getNewTask();
        $highPriorityTask->setAttributes([
            'project_id' => $project->getAttribute('_id'),
            'priority' => 'high',
            'skillset' => $skillSetMatch
        ]);
        $highPriorityTask->save();

        // Test task priority coefficient
        $pp = $this->getApplication()->getService(ProfilePerformance::class);
        $out = $pp->taskPriorityCoefficient($this->profile, $lowPriorityTask);
        $this->assertEquals(1.0, $out);
    }

    /**
     * Test profile performance task priority coefficient = 0.5
     */
    public function testProfilePerformanceTaskPriorityCoefficientMediumDeduction()
    {
        $this->markTestSkipped(
            'Mark as uncompleted. Need truncate method on repository to clear database records.'
        );
        // Get new project
        $project = $this->getNewProject();
        $members = [$this->profile->getAttribute('_id')];
        $project->setAttribute('members', $members);
        $project->save();

        // Create skillsets for tasks
        $skillSetMatch = [
            'PHP',
            'Planning',
            'React',
        ];

        // Create some tasks and set skillset
        $lowPriorityTask = $this->getNewTask();
        $lowPriorityTask->setAttributes([
            'project_id' => $project->getAttribute('_id'),
            'priority' => 'low',
            'skillset' => $skillSetMatch
        ]);
        $lowPriorityTask->save();

        $mediumPriorityTask = $this->getNewTask();
        $mediumPriorityTask->setAttributes([
            'project_id' => $project->getAttribute('_id'),
            'priority' => 'medium',
            'skillset' => $skillSetMatch
        ]);
        $mediumPriorityTask->save();

        $highPriorityTask = $this->getNewTask();
        $highPriorityTask->setAttributes([
            'project_id' => $project->getAttribute('_id'),
            'priority' => 'high',
            'skillset' => $skillSetMatch
        ]);
        $highPriorityTask->save();

        // Test task priority coefficient
        $pp = $this->getApplication()->getService(ProfilePerformance::class);
        $out = $pp->taskPriorityCoefficient($this->profile, $lowPriorityTask);
        $this->assertEquals(0.5, $out);
    }

    /**
     * Test profile performance task priority coefficient = 0.8
     */
    public function testProfilePerformanceTaskPriorityCoefficientHighDeduction()
    {
        $this->markTestSkipped(
            'Mark as uncompleted. Need truncate method on repository to clear database records.'
        );
        // Get new project
        $project = $this->getNewProject();
        $members = [$this->profile->getAttribute('_id')];
        $project->setAttribute('members', $members);
        $project->save();

        // Create skillsets for tasks
        $skillSetMatch = [
            'PHP',
            'Planning',
            'React',
        ];

        // Create some tasks and set skillset
        $lowPriorityTask = $this->getNewTask();
        $lowPriorityTask->setAttributes([
            'project_id' => $project->getAttribute('_id'),
            'priority' => 'low',
            'skillset' => $skillSetMatch
        ]);
        $lowPriorityTask->save();

        $mediumPriorityTask = $this->getNewTask();
        $mediumPriorityTask->setAttributes([
            'project_id' => $project->getAttribute('_id'),
            'priority' => 'medium',
            'skillset' => $skillSetMatch
        ]);
        $mediumPriorityTask->save();

        $highPriorityTask = $this->getNewTask();
        $highPriorityTask->setAttributes([
            'project_id' => $project->getAttribute('_id'),
            'priority' => 'high',
            'skillset' => $skillSetMatch
        ]);
        $highPriorityTask->save();

        // Test task priority coefficient
        $pp = $this->getApplication()->getService(ProfilePerformance::class);
        $out = $pp->taskPriorityCoefficient($this->profile, $lowPriorityTask);
        $this->assertEquals(0.8, $out);
    }
}
