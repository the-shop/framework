<?php

namespace Application\Test\Application\Listeners;

use Application\Exceptions\UserInputException;
use Application\Listeners\TaskClaim;
use Application\Test\Application\Traits\Helpers;
use Application\Test\Application\Traits\ProjectRelated;
use Framework\Base\Test\UnitTest;

class TaskClaimTest extends UnitTest
{
    use ProjectRelated, Helpers;

    /** @var  \Framework\Base\Model\BrunoInterface */
    private $task1;
    /** @var  \Framework\Base\Model\BrunoInterface */
    private $task2;
    /** @var  \Framework\Base\Model\BrunoInterface */
    private $project;
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
                                   'slack' => 'test'
                               ]
                           )
                           ->save();

        $this->project = $this->getNewProject()
                              ->save();

        $this->task1 = $this->getNewTask()
                            ->setAttribute('project_id', $this->project->getId())
                            ->save();

        $this->task2 = $this->getNewTask()
                            ->setAttribute('project_id', $this->project->getId())
                            ->setAttribute('passed_qa', false)
                            ->setAttribute('blocked', false)
                            ->setAttribute('qa_in_progress', false)
                            ->setAttribute('submitted_for_qa', false)
                            ->save();

        $this->listener = new TaskClaim();
        $this->listener->setApplication($this->getApplication());
        $this->getApplication()
             ->getConfiguration()
             ->setPathValue('internal.tasks.reservation.maxReservationTime', 30);
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->deleteCollection($this->project);
        $this->deleteCollection($this->user);
        $this->deleteCollection($this->task1);
    }

    public function testNotMemberOfProject()
    {
        $this->task1->setAttribute('owner', $this->user->getId());

        $this->expectException(UserInputException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage('Permission denied. Not a member of project.');

        $this->listener->handle($this->task1);
    }

    public function testPreviouslyReservedTask()
    {
        $this->task1->setAttribute('owner', $this->user->getId());

        $this->project->setAttribute('members', [$this->user->getId()])
                      ->save();

        $reservationsBy = [
            [
                'user_id' => $this->user->getId(),
                'timestamp' => (time() - 60)
            ],
        ];

        $this->task2->setAttribute('reservationsBy', $reservationsBy)
                    ->save();

        $this->expectException(UserInputException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage('Permission denied. There is reserved previous task.');

        $this->listener->handle($this->task1);
    }

    public function testUnfinishedPreviousTask()
    {
        $this->task1->setAttribute('owner', $this->user->getId());

        $this->project->setAttribute('members', [$this->user->getId()])
                      ->save();

        $this->task2->setAttribute('owner', $this->user->getId())
                    ->save();

        $this->expectException(UserInputException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage('Permission denied. There are unfinished previous tasks.');

        $this->listener->handle($this->task1);
    }
}
