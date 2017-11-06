<?php

namespace Application\Test\Application\Listeners;

use Application\Listeners\TaskStatisticsCalculation;
use Application\Test\Application\Traits\Helpers;
use Application\Test\Application\Traits\ProjectRelated;
use Framework\Base\Test\UnitTest;

class TaskStatisticsCalculationTest extends UnitTest
{
    use ProjectRelated, Helpers;

    /** @var  \Framework\Base\Model\BrunoInterface */
    private $task;
    /** @var  \Framework\Base\Model\BrunoInterface */
    private $user;
    /** @var  \Framework\Base\Event\ListenerInterface */
    private $listener;

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
                               ]
                           )
                           ->save();

        $this->task = $this->getNewTask()
                           ->save();

        $this->listener = (new TaskStatisticsCalculation())->setApplication($this->getApplication());
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->deleteCollection($this->user);
        $this->deleteCollection($this->task);
        $this->purgeCollection('profile_overall');
    }

    /**
     * Test task status time with new task that has owner
     */
    public function testTaskStatusTimeNewTaskWithOwnerAndNoUpdate()
    {
        $this->task->setAttribute('owner', $this->user->getId())
                   ->save();
        $this::assertEquals(false, $this->listener->handle($this->task));
    }

    /**
     * Test task status time for task assigned
     */
    public function testTaskStatusTimeCalculationForTaskAssigned()
    {
        $this->task->setAttribute('owner', $this->user->getId());
        $this->listener->handle($this->task);

        $work = $this->task->getAttribute('work');
        $owner = $this->task->getAttribute('owner');

        $this::assertArrayHasKey('worked', $work[$owner]);
        $this::assertArrayHasKey('paused', $work[$owner]);
        $this::assertArrayHasKey('qa', $work[$owner]);
        $this::assertArrayHasKey('qa_in_progress', $work[$owner]);
        $this::assertArrayHasKey('qa_total_time', $work[$owner]);
        $this::assertArrayHasKey('blocked', $work[$owner]);
        $this::assertArrayHasKey('workTrackTimestamp', $work[$owner]);
        $this::assertArrayHasKey('timeAssigned', $work[$owner]);
        $this::assertEquals(0, $work[$owner]['worked']);
        $this::assertEquals(0, $work[$owner]['paused']);
        $this::assertEquals(0, $work[$owner]['qa']);
        $this::assertEquals(0, $work[$owner]['qa_in_progress']);
        $this::assertEquals(0, $work[$owner]['qa_total_time']);
        $this::assertEquals(0, $work[$owner]['blocked']);
        $this::assertEquals($work[$owner]['workTrackTimestamp'], $work[$owner]['timeAssigned']);
    }

    /**
     * Test task status time for task reassigned
     */
    public function testTaskStatusTimeCalculationForTaskReassigned()
    {
        // Assigned 30 minutes ago
        $assignedAgo = time() - (30 * 60);
        $this->setTaskOwner($this->user);
        $task = $this->getAssignedTask($assignedAgo);
        $task->save();
        $oldWorkTrackTimestamp = $task->getAttribute('work')[$this->user->getId()]['workTrackTimestamp'];
        $newOwner = $this->getApplication()
                         ->getRepositoryManager()
                         ->getRepositoryFromResourceName('users')
                         ->newModel()
                         ->setAttributes(
                             [
                                 'name' => 'test user',
                                 'email' => $this->generateRandomEmail(20),
                                 'password' => 'test',
                             ]
                         )
                         ->save();
        $task->setAttribute('owner', $newOwner->getId());

        $this->listener->handle($task);

        $work = $task->getAttribute('work');
        $owner = $task->getAttribute('owner');
        $oldOwner = $this->user->getId();

        $this::assertCount(2, $work);
        $this::assertArrayHasKey($newOwner->getId(), $work);
        $this::assertArrayHasKey('worked', $work[$oldOwner]);
        $this::assertArrayHasKey('paused', $work[$oldOwner]);
        $this::assertArrayHasKey('qa', $work[$oldOwner]);
        $this::assertArrayHasKey('qa_in_progress', $work[$owner]);
        $this::assertArrayHasKey('qa_total_time', $work[$owner]);
        $this::assertArrayHasKey('blocked', $work[$owner]);
        $this::assertArrayHasKey('workTrackTimestamp', $work[$oldOwner]);
        $this::assertArrayHasKey('timeAssigned', $work[$oldOwner]);
        $this::assertArrayHasKey('timeRemoved', $work[$oldOwner]);
        $this::assertEquals(
            $work[$oldOwner]['workTrackTimestamp'] - $oldWorkTrackTimestamp,
            $work[$oldOwner]['worked']
        );
        $this::assertEquals(0, $work[$oldOwner]['paused']);
        $this::assertEquals(0, $work[$oldOwner]['qa']);
        $this::assertEquals(0, $work[$owner]['qa_in_progress']);
        $this::assertEquals(0, $work[$owner]['qa_total_time']);
        $this::assertEquals(0, $work[$oldOwner]['blocked']);
        $this::assertEquals($work[$oldOwner]['workTrackTimestamp'], $work[$oldOwner]['timeRemoved']);
        $this::assertArrayHasKey('worked', $work[$owner]);
        $this::assertArrayHasKey('paused', $work[$owner]);
        $this::assertArrayHasKey('qa', $work[$owner]);
        $this::assertArrayHasKey('qa_in_progress', $work[$owner]);
        $this::assertArrayHasKey('qa_total_time', $work[$owner]);
        $this::assertArrayHasKey('blocked', $work[$owner]);
        $this::assertArrayHasKey('workTrackTimestamp', $work[$owner]);
        $this::assertArrayHasKey('timeAssigned', $work[$owner]);
        $this::assertArrayNotHasKey('timeRemoved', $work[$owner]);
        $this::assertEquals(0, $work[$owner]['worked']);
        $this::assertEquals(0, $work[$owner]['paused']);
        $this::assertEquals(0, $work[$owner]['qa']);
        $this::assertEquals(0, $work[$owner]['qa_in_progress']);
        $this::assertEquals(0, $work[$owner]['qa_total_time']);
        $this::assertEquals(0, $work[$owner]['blocked']);
        $this::assertEquals($work[$owner]['workTrackTimestamp'], $work[$owner]['timeAssigned']);
    }

    /**
     * Test task status time for action
     */
    public function testTaskStatusTimeCalculationForTaskAction()
    {
        $arr = ['paused', 'blocked', 'submitted_for_qa'];
        $val = $arr[array_rand($arr)];
        // Assigned 30 minutes ago
        $now = time();
        $assignedAgo = $now - (30 * 60);
        $this->task->setAttributes(
            [
                'due_date' => ($now + (30 * 60)),
                'owner' => $this->user->getId(),
                'timeAssigned' => $assignedAgo,
                'timeFinished' => null,
                'work' => [
                    $this->user->getId() => [
                        'worked' => 0,
                        'paused' => 0,
                        'qa' => 0,
                        'qa_in_progress' => 0,
                        'qa_total_time' => 0,
                        'numberFailedQa' => 0,
                        'blocked' => 0,
                        'workTrackTimestamp' => $assignedAgo,
                        'timeAssigned' => $assignedAgo,
                    ],
                ],
            ]
        )
        ->save();

        $owner = $this->task->getAttribute('owner');
        $workedTimeBeforeListener = $this->task->getAttribute('work')[$owner]['worked'];
        $timeStampBeforeListener = $this->task->getAttribute('work')[$owner]['workTrackTimestamp'];

        $this->task->setAttribute($val, true);
        $this->listener->handle($this->task);

        $work = $this->task->getAttribute('work');
        $owner = $this->task->getAttribute('owner');

        $this::assertGreaterThan($workedTimeBeforeListener, $work[$owner]['worked']);
        $this::assertGreaterThan($timeStampBeforeListener, $work[$owner]['workTrackTimestamp']);
        $this::assertEquals(
            $work[$owner]['workTrackTimestamp'] - $timeStampBeforeListener,
            $work[$owner]['worked']
        );
    }

    /**
     * Test task status time for resumed
     */
    public function testTaskStatusTimeCalculationForTaskResumed()
    {
        $arr = ['paused', 'blocked'];
        $val = $arr[array_rand($arr)];
        // Assigned 30 minutes ago
        $now = time();
        $assignedAgo = $now - (30 * 60);
        $this->task->setAttributes(
            [
                'due_date' => ($now + (30 * 60)),
                'owner' => $this->user->getId(),
                'timeAssigned' => $assignedAgo,
                'timeFinished' => null,
                $val => true,
                'work' => [
                    $this->user->getId() => [
                        'worked' => 0,
                        'paused' => 0,
                        'qa' => 0,
                        'qa_in_progress' => 0,
                        'qa_total_time' => 0,
                        'numberFailedQa' => 0,
                        'blocked' => 0,
                        'workTrackTimestamp' => $assignedAgo,
                        'timeAssigned' => $assignedAgo,
                    ],
                ],
            ]
        )
        ->save();

        $owner = $this->task->getAttribute('owner');
        $timeBeforeListener = $this->task->getAttribute('work')[$owner][$val];
        $timeStampBeforeListener = $this->task->getAttribute('work')[$owner]['workTrackTimestamp'];

        $this->task->setAttribute($val, false);
        $this->listener->handle($this->task);

        $work = $this->task->getAttribute('work');
        $owner = $this->task->getAttribute('owner');

        $this::assertGreaterThan($timeBeforeListener, $work[$owner][$val]);
        $this::assertGreaterThan($timeStampBeforeListener, $work[$owner]['workTrackTimestamp']);
        $this::assertEquals(
            $work[$owner]['workTrackTimestamp'] - $timeStampBeforeListener,
            $work[$owner][$val]
        );
    }

    /**
     * Test task status time when task submitted for QA progress
     */
    public function testTaskStatusTimeCalculationForQaProgress()
    {
        // Assigned 30 minutes ago
        $now = time();
        $assignedAgo = $now - (30 * 60);
        $this->task->setAttributes(
            [
                'due_date' => ($now + (30 * 60)),
                'owner' => $this->user->getId(),
                'timeAssigned' => $assignedAgo,
                'timeFinished' => null,
                'submitted_for_qa' => true,
                'work' => [
                    $this->user->getId() => [
                        'worked' => 0,
                        'paused' => 0,
                        'qa' => 0,
                        'qa_in_progress' => 0,
                        'qa_total_time' => 0,
                        'numberFailedQa' => 0,
                        'blocked' => 0,
                        'workTrackTimestamp' => $assignedAgo,
                        'timeAssigned' => $assignedAgo,
                    ],
                ],
            ]
        )
        ->save();

        $owner = $this->task->getAttribute('owner');
        $timeBeforeListener = $this->task->getAttribute('work')[$owner]['qa'];
        $progressBeforeListener = $this->task->getAttribute('work')[$owner]['qa_in_progress'];
        $timeStampBeforeListener = $this->task->getAttribute('work')[$owner]['workTrackTimestamp'];


        $this->task->setAttribute('qa_in_progress', true);
        $this->listener->handle($this->task);

        $work = $this->task->getAttribute('work');
        $owner = $this->task->getAttribute('owner');

        $this::assertEquals(0, $progressBeforeListener);
        $this::assertEquals(0, $work[$owner]['qa_total_time']);
        $this::assertGreaterThan($timeBeforeListener, $work[$owner]['qa']);
        $this::assertGreaterThan($timeStampBeforeListener, $work[$owner]['workTrackTimestamp']);
        $this::assertEquals(
            $work[$owner]['workTrackTimestamp'] - $timeStampBeforeListener,
            $work[$owner]['qa']
        );
    }

    /**
     * Test task status time for failed QA
     */
    public function testTaskStatusTimeCalculationForFailedQa()
    {
        // Assigned 30 minutes ago
        $now = time();
        $assignedAgo = $now - (30 * 60);
        $this->task->setAttributes(
            [
                'due_date' => ($now + (30 * 60)),
                'owner' => $this->user->getId(),
                'timeAssigned' => $assignedAgo,
                'timeFinished' => null,
                'qa_in_progress' => true,
                'work' => [
                    $this->user->getId() => [
                        'worked' => 0,
                        'paused' => 0,
                        'qa' => 0,
                        'qa_in_progress' => 0,
                        'qa_total_time' => 0,
                        'numberFailedQa' => 0,
                        'blocked' => 0,
                        'workTrackTimestamp' => $assignedAgo,
                        'timeAssigned' => $assignedAgo,
                    ],
                ],
            ]
        )
        ->save();

        $owner = $this->task->getAttribute('owner');
        $timeBeforeListener = $this->task->getAttribute('work')[$owner]['qa'];
        $progressBeforeListener = $this->task->getAttribute('work')[$owner]['qa_in_progress'];
        $timeStampBeforeListener = $this->task->getAttribute('work')[$owner]['workTrackTimestamp'];


        $this->task->setAttribute('qa_in_progress', false);
        $this->listener->handle($this->task);

        $work = $this->task->getAttribute('work');
        $owner = $this->task->getAttribute('owner');

        $this::assertEquals(0, $timeBeforeListener);
        $this::assertEquals(0, $work[$owner]['qa_in_progress']);
        $this::assertEquals($progressBeforeListener, $work[$owner]['qa_in_progress']);
        $this::assertGreaterThan($timeStampBeforeListener, $work[$owner]['workTrackTimestamp']);
        $this::assertEquals(
            $work[$owner]['workTrackTimestamp'] - $timeStampBeforeListener,
            $work[$owner]['qa_total_time']
        );
    }

    /**
     * Test task status time for passed QA
     */
    public function testTaskStatusTimeCalculationForPassedQa()
    {
        // Assigned 30 minutes ago
        $now = time();
        $assignedAgo = $now - (30 * 60);
        $this->task->setAttributes(
            [
                'due_date' => ($now + (30 * 60)),
                'owner' => $this->user->getId(),
                'timeAssigned' => $assignedAgo,
                'timeFinished' => null,
                'qa_in_progress' => true,
                'work' => [
                    $this->user->getId() => [
                        'worked' => 0,
                        'paused' => 0,
                        'qa' => 0,
                        'qa_in_progress' => 0,
                        'qa_total_time' => 0,
                        'numberFailedQa' => 0,
                        'blocked' => 0,
                        'workTrackTimestamp' => $assignedAgo,
                        'timeAssigned' => $assignedAgo,
                    ],
                ],
            ]
        )
        ->save();

        $owner = $this->task->getAttribute('owner');
        $progressBeforeListener = $this->task->getAttribute('work')[$owner]['qa_in_progress'];
        $timeStampBeforeListener = $this->task->getAttribute('work')[$owner]['workTrackTimestamp'];

        $this->task->setAttribute('qa_in_progress', false);
        $this->task->setAttribute('passed_qa', true);
        $this->listener->handle($this->task);

        $work = $this->task->getAttribute('work');
        $owner = $this->task->getAttribute('owner');

        $this::assertGreaterThan($progressBeforeListener, $work[$owner]['qa_in_progress']);
        $this::assertGreaterThan($timeStampBeforeListener, $work[$owner]['workTrackTimestamp']);
        $this::assertEquals(
            $work[$owner]['workTrackTimestamp'] - $timeStampBeforeListener,
            $work[$owner]['qa_in_progress']
        );
        $this::assertEquals(
            $work[$owner]['qa_in_progress'],
            $work[$owner]['qa_total_time']
        );
    }

    /**
     * Test task status time calculation complex flow (Reassigned, paused, blocked, qa, fail qa and finally done)
     */
    public function testTaskStatusTimeComplexFlowTaskDone()
    {
        // Assigned 30 minutes ago
        $now = time();
        $assignedAgo = $now - (30 * 60);
        $this->task->setAttributes(
            [
                'due_date' => ($now + (30 * 60)),
                'owner' => $this->user->getId(),
                'timeAssigned' => $assignedAgo,
                'timeFinished' => null,
                'work' => [
                    $this->user->getId() => [
                        'worked' => 0,
                        'paused' => 0,
                        'qa' => 0,
                        'qa_in_progress' => 0,
                        'qa_total_time' => 0,
                        'numberFailedQa' => 0,
                        'blocked' => 0,
                        'workTrackTimestamp' => $assignedAgo,
                        'timeAssigned' => $assignedAgo,
                    ],
                ],
            ]
        )
        ->save();

        $owner = $this->task->getAttribute('owner');
        $workedTimeBeforeListener = $this->task->getAttribute('work')[$owner]['worked'];
        $timeStampBeforeListener = $this->task->getAttribute('work')[$owner]['workTrackTimestamp'];

        $this->task->setAttribute('paused', true);
        $this->listener->handle($this->task);

        $owner = $this->task->getAttribute('owner');
        $work = $this->task->getAttribute('work');

        $this::assertGreaterThan($workedTimeBeforeListener, $work[$owner]['worked']);
        $this::assertGreaterThan($timeStampBeforeListener, $work[$owner]['workTrackTimestamp']);
        $this::assertEquals(
            $work[$owner]['workTrackTimestamp'] - $timeStampBeforeListener,
            $work[$owner]['worked']
        );

        //task resumed
        $work[$owner]['workTrackTimestamp'] = time() - 20 * 60;
        $this->task->setAttribute('work', $work);
        $this->task->save();

        $pausedTimeBeforeListener = $work[$owner]['paused'];
        $timeStampBeforeResumed = $work[$owner]['workTrackTimestamp'];

        $this->task->setAttribute('paused', false);
        $this->listener->handle($this->task);

        $owner = $this->task->getAttribute('owner');
        $work = $this->task->getAttribute('work');

        $this::assertGreaterThan($pausedTimeBeforeListener, $work[$owner]['paused']);
        $this::assertGreaterThan($timeStampBeforeResumed, $work[$owner]['workTrackTimestamp']);
        $this::assertEquals(
            $work[$owner]['workTrackTimestamp'] - $timeStampBeforeResumed,
            $work[$owner]['paused']
        );

        //task reassigned
        $work[$owner]['workTrackTimestamp'] = time() - 15 * 60;
        $this->task->setAttribute('work', $work);
        $this->task->save();

        $oldOwner = $owner;
        $oldWorkTrackTimestamp = $work[$oldOwner]['workTrackTimestamp'];
        $oldWorked = $work[$oldOwner]['worked'];
        $oldWorkPaused = $work[$oldOwner]['paused'];
        $oldWorkQa = $work[$oldOwner]['qa'];
        $oldWorkBlocked = $work[$oldOwner]['blocked'];
        $oldWorkQaProgress = $work[$oldOwner]['qa_in_progress'];
        $oldWorkQaProgressTotal = $work[$oldOwner]['qa_total_time'];

        $newOwner = $this->getApplication()
                         ->getRepositoryManager()
                         ->getRepositoryFromResourceName('users')
                         ->newModel()
                         ->setAttributes(
                             [
                                 'name' => 'test user2',
                                 'email' => $this->generateRandomEmail(20),
                                 'password' => 'test',
                             ]
                         )
                         ->save();

        $this->task->setAttribute('owner', $newOwner->getId());

        $this->listener->handle($this->task);

        $owner = $this->task->getAttribute('owner');
        $work = $this->task->getAttribute('work');

        $this::assertCount(2, $work);
        $this::assertArrayHasKey($newOwner->getId(), $work);

        $this::assertArrayHasKey('worked', $work[$oldOwner]);
        $this::assertArrayHasKey('paused', $work[$oldOwner]);
        $this::assertArrayHasKey('qa', $work[$oldOwner]);
        $this::assertArrayHasKey('qa_in_progress', $work[$owner]);
        $this::assertArrayHasKey('qa_total_time', $work[$owner]);
        $this::assertArrayHasKey('blocked', $work[$owner]);
        $this::assertArrayHasKey('workTrackTimestamp', $work[$oldOwner]);
        $this::assertArrayHasKey('timeAssigned', $work[$oldOwner]);
        $this::assertArrayHasKey('timeRemoved', $work[$oldOwner]);
        $this::assertEquals(
            $work[$oldOwner]['workTrackTimestamp'] - $oldWorkTrackTimestamp + $oldWorked,
            $work[$oldOwner]['worked']
        );
        $this::assertEquals($oldWorkPaused, $work[$oldOwner]['paused']);
        $this::assertEquals($oldWorkQa, $work[$oldOwner]['qa']);
        $this::assertEquals($oldWorkQaProgress, $work[$oldOwner]['qa_in_progress']);
        $this::assertEquals($oldWorkQaProgressTotal, $work[$oldOwner]['qa_total_time']);
        $this::assertEquals($oldWorkBlocked, $work[$oldOwner]['blocked']);
        $this::assertEquals(
            $work[$oldOwner]['workTrackTimestamp'],
            $work[$oldOwner]['timeRemoved']
        );

        $this::assertArrayHasKey('worked', $work[$owner]);
        $this::assertArrayHasKey('paused', $work[$owner]);
        $this::assertArrayHasKey('qa', $work[$owner]);
        $this::assertArrayHasKey('qa_in_progress', $work[$owner]);
        $this::assertArrayHasKey('qa_total_time', $work[$owner]);
        $this::assertArrayHasKey('blocked', $work[$owner]);
        $this::assertArrayHasKey('workTrackTimestamp', $work[$owner]);
        $this::assertArrayHasKey('timeAssigned', $work[$owner]);
        $this::assertArrayNotHasKey('timeRemoved', $work[$owner]);
        $this::assertEquals(0, $work[$owner]['worked']);
        $this::assertEquals(0, $work[$owner]['paused']);
        $this::assertEquals(0, $work[$owner]['qa']);
        $this::assertEquals(0, $work[$owner]['qa_in_progress']);
        $this::assertEquals(0, $work[$owner]['qa_total_time']);
        $this::assertEquals(0, $work[$owner]['blocked']);
        $this::assertEquals(
            $work[$owner]['workTrackTimestamp'],
            $work[$owner]['timeAssigned']
        );

        //qa ready
        $work[$owner]['workTrackTimestamp'] = time() - 13 * 60;
        $work[$owner]['timeAssigned'] = time() - 13 * 60;
        $this->task->setAttribute('work', $work);
        $this->task->save();

        $workedTimeBeforeListener = $work[$owner]['worked'];
        $timeStampBeforeListener = $work[$owner]['workTrackTimestamp'];

        $this->task->setAttribute('submitted_for_qa', true);
        $this->listener->handle($this->task);

        $work = $this->task->getAttribute('work');

        $this::assertGreaterThan($workedTimeBeforeListener, $work[$owner]['worked']);
        $this::assertGreaterThan($timeStampBeforeListener, $work[$owner]['workTrackTimestamp']);
        $this::assertEquals(
            $work[$owner]['workTrackTimestamp'] - $timeStampBeforeListener,
            $work[$owner]['worked']
        );

        //task added to QA progress
        $work[$owner]['workTrackTimestamp'] = time() - 11 * 60;
        $this->task->setAttribute('work', $work);
        $this->task->save();

        $qaTimeBeforeListener = $work[$owner]['qa'];
        $timeStampBeforeListener = $work[$owner]['workTrackTimestamp'];

        $this->task->setAttribute('qa_in_progress', true);
        $this->task->setAttribute('submitted_for_qa', false);
        $this->listener->handle($this->task);

        $work = $this->task->getAttribute('work');

        $this::assertEquals(0, $work[$owner]['qa_in_progress']);
        $this::assertEquals(0, $work[$owner]['qa_total_time']);
        $this::assertGreaterThan($qaTimeBeforeListener, $work[$owner]['qa']);
        $this::assertGreaterThan($timeStampBeforeListener, $work[$owner]['workTrackTimestamp']);
        $this::assertEquals(
            $work[$owner]['workTrackTimestamp'] - $timeStampBeforeListener,
            $work[$owner]['qa']
        );

        //qa failed
        $work[$owner]['workTrackTimestamp'] = time() - 9 * 60;
        $this->task->setAttribute('work', $work);
        $this->task->save();

        $qaProgressTimeBeforeListener = $work[$owner]['qa_in_progress'];
        $qaProgressTotalTimeBeforeListener = $work[$owner]['qa_total_time'];
        $timeStampBeforeListener = $work[$owner]['workTrackTimestamp'];

        $this->task->setAttribute('qa_in_progress', false);
        $this->listener->handle($this->task);

        $work = $this->task->getAttribute('work');

        $this::assertEquals($qaProgressTimeBeforeListener, $work[$owner]['qa_in_progress']);

        //when task failed QA qa_in_progress should be zero
        $this::assertEquals(0, $work[$owner]['qa_in_progress']);
        $this::assertGreaterThan($timeStampBeforeListener, $work[$owner]['workTrackTimestamp']);
        $this::assertGreaterThan($qaProgressTotalTimeBeforeListener, $work[$owner]['qa_total_time']);

        //check QA total time when task failed QA
        $this::assertEquals(
            $work[$owner]['workTrackTimestamp'] - $timeStampBeforeListener,
            $work[$owner]['qa_total_time']
        );

        //task resumed
        $work[$owner]['workTrackTimestamp'] = time() - 8 * 60;
        $this->task->setAttribute('work', $work);
        $this->task->setAttribute('paused', true);
        $this->task->save();

        $pausedTimeBeforeListener = $work[$owner]['paused'];
        $timeStampBeforeResumed = $work[$owner]['workTrackTimestamp'];

        $this->task->setAttribute('paused', false);
        $this->listener->handle($this->task);

        $work = $this->task->getAttribute('work');

        $this::assertGreaterThan($pausedTimeBeforeListener, $work[$owner]['paused']);
        $this::assertGreaterThan($timeStampBeforeResumed, $work[$owner]['workTrackTimestamp']);
        $this::assertEquals(
            ($work[$owner]['workTrackTimestamp'] - $timeStampBeforeResumed),
            $work[$owner]['paused']
        );

        //task submitted for qa again
        $work[$owner]['workTrackTimestamp'] = time() - 6 * 60;
        $this->task->setAttribute('work', $work);
        $this->task->save();

        $workedTimeBeforeListener = $work[$owner]['worked'];
        $timeStampBeforeListener = $work[$owner]['workTrackTimestamp'];

        $this->task->setAttribute('submitted_for_qa', true);
        $this->listener->handle($this->task);

        $work = $this->task->getAttribute('work');

        $this::assertGreaterThan($workedTimeBeforeListener, $work[$owner]['worked']);
        $this::assertGreaterThan($timeStampBeforeListener, $work[$owner]['workTrackTimestamp']);
        $this::assertEquals($work[$owner]['workTrackTimestamp'] - $timeStampBeforeListener
                            + $workedTimeBeforeListener, $work[$owner]['worked']);

        //task added to QA progress again
        $work[$owner]['workTrackTimestamp'] = time() - 5 * 60;
        $this->task->setAttribute('work', $work);
        $this->task->save();

        $qaTimeBeforeListener = $work[$owner]['qa'];
        $timeStampBeforeListener = $work[$owner]['workTrackTimestamp'];
        $qaProgressTotalTimeBeforeListener = $work[$owner]['qa_total_time'];

        $this->task->setAttribute('qa_in_progress', true);
        $this->task->setAttribute('submitted_for_qa', false);
        $this->listener->handle($this->task);

        $work = $this->task->getAttribute('work');

        $this::assertEquals(0, $work[$owner]['qa_in_progress']);
        $this::assertEquals($qaProgressTotalTimeBeforeListener, $work[$owner]['qa_total_time']);
        $this::assertGreaterThan($qaTimeBeforeListener, $work[$owner]['qa']);
        $this::assertGreaterThan($timeStampBeforeListener, $work[$owner]['workTrackTimestamp']);
        //check QA time
        $this::assertEquals($work[$owner]['workTrackTimestamp'] - $timeStampBeforeListener
                            + $qaTimeBeforeListener, $work[$owner]['qa']);

        //finally QA passed
        $work[$owner]['workTrackTimestamp'] = time() - 2 * 60;
        $this->task->setAttribute('work', $work);
        $this->task->save();

        $qaProgressTimeBeforeListener = $work[$owner]['qa_in_progress'];
        $qaProgressTotalTimeBeforeListener = $work[$owner]['qa_total_time'];
        $timeStampBeforeListener = $work[$owner]['workTrackTimestamp'];

        $this->task->setAttribute('passed_qa', true);
        $this->task->setAttribute('qa_in_progress', false);
        $this->listener->handle($this->task);

        $work = $this->task->getAttribute('work');

        $this::assertGreaterThan($qaProgressTimeBeforeListener, $work[$owner]['qa_in_progress']);
        $this::assertGreaterThan($timeStampBeforeListener, $work[$owner]['workTrackTimestamp']);
        $this::assertGreaterThan($qaProgressTotalTimeBeforeListener, $work[$owner]['qa_total_time']);
        //check QA total time and QA in progress time
        $this::assertEquals($work[$owner]['workTrackTimestamp'] - $timeStampBeforeListener
                            + $qaProgressTotalTimeBeforeListener, $work[$owner]['qa_total_time']);
        $this::assertEquals(
            $work[$owner]['workTrackTimestamp'] - $timeStampBeforeListener,
            $work[$owner]['qa_in_progress']
        );
    }
}
