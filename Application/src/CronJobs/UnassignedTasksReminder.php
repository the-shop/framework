<?php

namespace Application\CronJobs\Commands;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Helpers\Parse;
use Framework\Base\Model\BrunoInterface;
use Framework\Terminal\Commands\Cron\CronJob;
use Application\CrudApi\Model\Generic;

/**
 * Class UnassignedTasksReminder
 * @package Application\CronJobs\Commands
 */
class UnassignedTasksReminder extends CronJob
{
    use ApplicationAwareTrait;

    /**
     * Execute the console command.
     */
    public function execute()
    {
        $repository = $this->getApplication()
            ->getRepositoryManager()
            ->getRepository(Generic::class);

        $projects = $repository->setResourceName('projects')->loadMultiple();

        $activeProjects = [];
        $members = [];
        $sprints = [];
        $tasks = [];

        $dateCheck = (new \DateTime())->format('Y-m-d');

        // Get all active projects, members of projects and sprints
        foreach ($projects as $project) {
            $projectAttributes = $project->getAttributes();
            if (empty($projectAttributes['acceptedBy']) !== true
                && $projectAttributes['isComplete'] !== true
            ) {
                $activeProjects[$projectAttributes['_id']] = $project;
                $projectSprints = $repository->setResourceName('sprints')
                    ->loadMultiple([
                        'project_id' => $projectAttributes['_id'],
                    ]);
                foreach ($projectSprints as $sprint) {
                    $sprintStartDueDate =
                        \DateTime::createFromFormat(
                            'U',
                            Parse::unixTimestamp($sprint->getAttribute('start'))
                        )->format('Y-m-d');
                    $sprintEndDueDate =
                        \DateTime::createFromFormat(
                            'U',
                            Parse::unixTimestamp($sprint->getAttribute('end'))
                        )->format('Y-m-d');
                    if ($dateCheck >= $sprintStartDueDate && $dateCheck <= $sprintEndDueDate) {
                        $sprints[$sprint->getAttribute('_id')] = $sprint;
                    }
                }

                if (empty($projectAttributes['members']) !== true) {
                    foreach ($projectAttributes['members'] as $memberId) {
                        $member = $repository->setResourceName('users')
                            ->loadOne($memberId);

                        $members[$memberId] = $member;
                    }
                }
            }
        }

        // Get all active tasks
        /**
         * @var BrunoInterface $sprint
         */
        foreach ($sprints as $sprint) {
            $sprintTasks = $repository->setResourceName('tasks')
                ->loadMultiple([
                    'sprint_id' => $sprint->getAttribute('_id'),
                ]);
            foreach ($sprintTasks as $task) {
                if (empty($task->getAttribute('owner')) === true) {
                    $tasks[$task->getAttribute('_id')] = $task;
                }
            }
        }

        // Ping on slack all users on active projects about unassigned tasks on active sprints
        $taskCount = [];

        foreach ($tasks as $task) {
            $taskProjectId = $task->getAttribute('project_id');
            if (array_key_exists($taskProjectId, $taskCount) !== true) {
                $taskCount[$taskProjectId] = 1;
            } else {
                $taskCount[$taskProjectId]++;
            }
        }

        if (empty($taskCount) !== true) {
            foreach ($activeProjects as $project) {
                if (array_key_exists($project->getAttribute('_id'), $taskCount) !== true) {
                    continue;
                }

                if (empty($project->getAttribute('members')) === true) {
                    continue;
                }

                $unassignedTasks = $taskCount[$project->getAttribute('_id')];
                $message = '*Reminder*:'
                    . 'There are * '
                    . $unassignedTasks
                    . '* unassigned tasks on active sprints'
                    . ', for project *'
                    . $project->getAttribute('name')
                    . '*';

                foreach ($members as $member) {
                    $memberAttributes = $member->getAttributes();
                    if (in_array($memberAttributes['_id'], $project->getAttribute('members'))
                        === true
                        && array_key_exists('slack', $memberAttributes) === true
                        && empty($memberAttributes['slack']) !== true
                        && $memberAttributes['active'] === true
                    ) {
                        $recipient = '@' . $memberAttributes['slack'];
                        //TODO: implement after slack service is implemented
                        // Slack::sendMessage($recipient, $message, Slack::MEDIUM_PRIORITY);
                    }
                }
            }
        }
    }
}
