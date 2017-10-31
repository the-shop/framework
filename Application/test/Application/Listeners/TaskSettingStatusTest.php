<?php

namespace Application\Test\Application\Listeners;

use Application\Listeners\TaskSettingStatus;
use Application\Test\Application\Traits\Helpers;
use Application\Test\Application\Traits\ProjectRelated;
use Application\Exceptions\UserInputException;
use Framework\Base\Test\UnitTest;

class TaskSettingStatusTest extends UnitTest
{
    use ProjectRelated, Helpers;

    /** @var  \Framework\Base\Model\BrunoInterface */
    private $task;

    public function setUp()
    {
        parent::setUp();

        $this->task = $this->getNewTask();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->deleteCollection($this->task);
    }

    public function testHandle()
    {
        $keysToCheck = ['paused', 'submitted_for_qa', 'passed_qa'];
        $this->task->setAttribute($keysToCheck[rand(0, 2)], true);

        $listener = new TaskSettingStatus();

        $this->expectException(UserInputException::class);
        $this->expectExceptionMessage('Permission denied. Task is not claimed.');
        $this->expectExceptionCode(403);

        $listener->handle($this->task);
    }
}
