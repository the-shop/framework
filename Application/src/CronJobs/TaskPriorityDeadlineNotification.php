<?php

namespace Application\CronJobs;

use Application\Services\SlackService;
use Framework\Terminal\Commands\Cron\CronJob;

class TaskPriorityDeadlineNotification extends CronJob
{
    const HIGH = 'High';
    const MEDIUM = 'Medium';
    const LOW = 'Low';

    public function execute()
    {
        // Get all tasks with due_date within next 28 days
        $now = time();
        $unixTime2Days = $now + (2 * 24 * 60 * 60);
        $unixTime7Days = $now + (7 * 24 * 60 * 60);
        $unixTime14Days = $now + (14 * 24 * 60 * 60);
        $unixTime28Days = $now + (28 * 24 * 60 * 60);

        $service = $this->getApplication()
                        ->getService(SlackService::class);

        if ($service->getApiClient() === null) {
            $service->setApiClient();
        }

        $repository = $this->getApplication()
                           ->getRepositoryManager()
                           ->getRepositoryFromResourceName('tasks');

        $query = $repository->createNewQueryForModel($repository->newModel())
                            ->addAndCondition('due_date', '<=', $unixTime28Days);

        $tasks = $repository->loadMultiple($query);

        $tasksDueDates = [];

        foreach ($tasks as $task) {
            if ($task->getAttribute('owner') === null) {
                $taskDueDate = $this->formatUnixTimeStamp($task->getAttribute('due_date'));
                if (key_exists($task->getAttribute('project_id'), $tasksDueDates) === false) {
                    // Check if task priority is High and due_date is between next 2-7 days, add counter
                    if (($taskDueDate > $unixTime2Days) === true
                        && ($taskDueDate <= $unixTime7Days) === true
                        && $task->getAttribute('priority') === self::HIGH
                    ) {
                        $tasksDueDates[$task->getAttribute('project_id')] =
                            $this->getTaskDueDateArrayStructure(self::HIGH);
                    } // Check if task priority is Medium and due_date is between next 8-14 days, add counter
                    elseif (($taskDueDate > $unixTime7Days) === true
                            && ($taskDueDate <= $unixTime14Days) === true
                            && $task->getAttribute('priority') === self::MEDIUM
                    ) {
                        $tasksDueDates[$task->getAttribute('project_id')] =
                            $this->getTaskDueDateArrayStructure(self::MEDIUM);
                    } // Check if task priority is Low and due_date is between next 15-28 days, add counter
                    elseif (($taskDueDate > $unixTime14Days) === true
                            && ($taskDueDate <= $unixTime28Days) === true
                            && $task->getAttribute('priority') === self::LOW
                    ) {
                        $tasksDueDates[$task->getAttribute('project_id')] =
                            $this->getTaskDueDateArrayStructure(self::LOW);
                    }
                } else {
                    // Check if task priority is High and due_date is between next 2-7 days, add counter
                    if (($taskDueDate > $unixTime2Days) === true
                        && ($taskDueDate <= $unixTime7Days) === true
                        && $task->getAttribute('priority') === self::HIGH
                    ) {
                        $tasksDueDates[$task->getAttribute('project_id')]['High'] ++;
                    } // Check if task priority is Medium and due_date is between next 8-14 days, add counter
                    elseif (($taskDueDate > $unixTime7Days) === true
                            && ($taskDueDate <= $unixTime14Days) === true
                            && $task->getAttribute('priority') === self::MEDIUM
                    ) {
                        $tasksDueDates[$task->getAttribute('project_id')]['Medium'] ++;
                    } // Check if task priority is Low and due_date is between next 15-28 days, add counter
                    elseif (($taskDueDate > $unixTime14Days) === true
                            && ($taskDueDate <= $unixTime28Days) === true
                            && $task->getAttribute('priority') === self::LOW
                    ) {
                        $tasksDueDates[$task->getAttribute('project_id')]['Low'] ++;
                    }
                }
            }
        }

        $projectOwnerIds = [];
        /** @var \Framework\Base\Model\BrunoInterface[] $projects */
        $projects = [];

        // Get all tasks projects and project owner IDs
        $repository = $this->getApplication()
                           ->getRepositoryManager()
                           ->getRepositoryFromResourceName('projects');

        foreach ($tasksDueDates as $projectId => $taskCount) {
            $project = $repository->loadOne($projectId);
            $projects[$projectId] = $project;
            if ($project->getAttribute('acceptedBy') !== null) {
                $projectOwnerIds[] = $project->getAttribute('acceptedBy');
            }
        }

        $repository = $this->getApplication()
                           ->getRepositoryManager()
                           ->getRepositoryFromResourceName('users');

        $recipients = $repository->loadMultiple();

        // Send slack notification to all active admins and POs about task priority deadlines
        foreach ($recipients as $recipient) {
            if (($recipient->getAttribute('admin') === true
                 || in_array($recipient->getId(), $projectOwnerIds) === true) === true
                && $recipient->getAttribute('slack') !== null
                && $recipient->getAttribute('active') === true
            ) {
                $sendTo = $recipient->getAttribute('slack');
                foreach ($projects as $projectToNotify) {
                    if ($recipient->getAttribute('admin') !== true
                        && $recipient->getId() !== $projectToNotify->getAttribute('acceptedBy')
                    ) {
                        continue;
                    }

                    /* Send notification per project about task deadlines for High priority in next 7 days,
                    Medium priority in next 14 days, and low priority in next 28 days*/
                    if (key_exists($projectToNotify->getId(), $tasksDueDates) === true) {
                        foreach ($tasksDueDates[$projectToNotify->getId()] as $priority => $tasksCounted) {
                            $message =
                                'On project *'
                                . $projectToNotify->getAttribute('name')
                                . '*, there are *'
                                . $tasksCounted;
                            if ($priority === self::HIGH) {
                                $message .= '* tasks with *High priority* in next *7 days*';
                            }
                            if ($priority === self::MEDIUM) {
                                $message .= '* tasks with *Medium priority* in next *14 days*';
                            }
                            if ($priority === self::LOW) {
                                $message .= '* tasks with *Low priority* in next *28 days*';
                            }
                            $service->setMessage($sendTo, $message, false, SlackService::LOW_PRIORITY);
                        }
                    }
                }
            }
        }
    }

    /**
     * Helper to get array with proper structure for task due dates counting
     * @param $priority
     * @return array
     */
    private function getTaskDueDateArrayStructure($priority)
    {
        $taskDueDates = [
            'High' => 0,
            'Medium' => 0,
            'Low' => 0
        ];

        if ($priority === self::HIGH) {
            $taskDueDates['High']++;
        }
        if ($priority === self::MEDIUM) {
            $taskDueDates['Medium']++;
        }
        if ($priority === self::LOW) {
            $taskDueDates['Medium']++;
        }

        return $taskDueDates;
    }
}
