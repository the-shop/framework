<?php

namespace Application\Test\Application\Traits;

use Framework\Base\Model\BrunoInterface;

trait ProjectRelated
{
    public function setTaskOwner(BrunoInterface $owner)
    {
        $this->profile = $owner;
    }

    /*
    |--------------------------------------------------------------------------
    | Get methods
    |--------------------------------------------------------------------------
    |
    | Here are getter methods for tests related to projects(tasks,user XP, profile performance etc.)
    */

    /**
     * Get new task without owner
     * @return BrunoInterface
     */
    public function getNewTask()
    {
        return $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('tasks')
            ->newModel()
            ->setAttributes(
                [
                    'title' => 'test task',
                    'owner' => '',
                    'paused' => false,
                    'submitted_for_qa' => false,
                    'qa_in_progress' => false,
                    'blocked' => false,
                    'passed_qa' => false,
                    'skillset' => [],
                ]
            );
    }

    /**
     * Get new project
     * @return BrunoInterface
     */
    public function getNewProject()
    {
        return $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('projects')
            ->newModel()
            ->setAttributes(
                [
                    'name' => 'Test Project',
                    'acceptedBy' => '',
                    'members' => [],
                ]
            );
    }

    /**
     * Get new archived project
     * @return BrunoInterface
     */
    public function getNewArchivedProject()
    {
        return $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('projects_archived')
            ->newModel()
            ->setAttributes(
                [
                    'name' => 'Test Project',
                    'acceptedBy' => '',
                    'members' => [],
                ]
            );
    }

    /**
     * Get new deleted project
     * @return BrunoInterface
     */
    public function getNewDeletedProject()
    {
        return $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('projects_deleted')
            ->newModel()
            ->setAttributes(
                [
                    'name' => 'Test Project',
                    'acceptedBy' => '',
                    'members' => [],
                ]
            );
    }

    /**
     * Get new sprint
     * @return BrunoInterface
     */
    public function getNewSprint()
    {
        return $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('sprints')
            ->newModel()
            ->setAttributes(
                [
                    'project_id' => '',
                    'title' => 'Test Sprint',
                ]
            );
    }

    /**
     * Get assigned task
     * @return BrunoInterface
     */
    public function getAssignedTask($timestamp = null)
    {
        if (!$timestamp) {
            $time = new \DateTime();
            $timestamp = $time->format('U');
        }

        $task = $this->getNewTask();

        $task->setAttributes(
            [
                'due_date' => $timestamp,
                'owner' => $this->profile->getAttribute('_id'),
                'timeAssigned' => (int)$timestamp,
                'timeFinished' => null,
                'work' => [
                    $this->profile->getAttribute('_id') => [
                        'worked' => 0,
                        'paused' => 0,
                        'qa' => 0,
                        'qa_in_progress' => 0,
                        'qa_total_time' => 0,
                        'numberFailedQa' => 0,
                        'blocked' => 0,
                        'workTrackTimestamp' => $timestamp,
                        'timeAssigned' => $timestamp,
                    ],
                ],

            ]
        );

        return $task;
    }

    /**
     * @param null $timestamp
     * @return BrunoInterface
     */
    public function getTaskWithJustAssignedHistory($timestamp = null)
    {
        if (!$timestamp) {
            $time = new \DateTime();
            $timestamp = $time->format('U');
        }

        $unixNow = (int)(new \DateTime())->format('U');

        $task = $this->getAssignedTask($timestamp);

        $task->setAttributes([
            'work' => [
                $this->profile->getAttribute('_id') => [
                    'worked' => $unixNow - $timestamp,
                    'paused' => 0,
                    'qa' => 0,
                    'qa_in_progress' => 0,
                    'qa_total_time' => 0,
                    'numberFailedQa' => 0,
                    'blocked' => 0,
                    'workTrackTimestamp' => (int)(new \DateTime())->format('U'),
                ],
            ],
            'task_history' => [
                [
                    'event' => 'Task assigned to sample user',
                    'status' => 'assigned',
                    'user' => $this->profile->getAttribute('_id'),
                    'timestamp' => $timestamp,
                ],
            ],
        ]);

        return $task;
    }
}
