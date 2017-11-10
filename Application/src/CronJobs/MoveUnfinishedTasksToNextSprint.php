<?php

namespace Application\CronJobs;

use Framework\CrudApi\Model\Generic;
use Application\Services\SlackService;
use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Helpers\Parse;
use Framework\Base\Model\BrunoInterface;
use Framework\Terminal\Commands\Cron\CronJob;

/**
 * Class MoveUnfinishedTasksToNextSprint
 * @package Application\CronJobs
 */
class MoveUnfinishedTasksToNextSprint extends CronJob
{
    use ApplicationAwareTrait;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function execute()
    {
        $repository = $this->getApplication()
            ->getRepositoryManager()
            ->getRepository(Generic::class);
        // Get all projects
        $projects = $repository->setResourceName('projects')->loadMultiple();

        // Get all admin users
        $admins = $repository->setResourceName('users')
            ->loadMultiple(['admin' => true]);

        $activeProjects = [];
        $sprints = [];

        // Get all active projects and project sprints
        foreach ($projects as $project) {
            $projectAttributes = $project->getAttributes();
            if (isset($projectAttributes['acceptedBy']) === true
                && empty($projectAttributes['acceptedBy']) === false
                && isset($projectAttributes['isComplete']) === true
                && $projectAttributes['isComplete'] === false
            ) {
                $activeProjects[$projectAttributes['_id']] = $project;
                $projectSprints = $repository->setResourceName('sprints')
                    ->loadMultiple(['project_id' => $project->getAttribute('_id')]);
                foreach ($projectSprints as $projectSprint) {
                    $sprints[$projectSprint->getAttribute('_id')] = $projectSprint;
                }
            }
        }

        $sprintEndedTasks = [];
        $endedSprints = [];
        $futureSprints = [];
        $futureSprintsStartDates = [];

        $unixNow = (new \DateTime())->format('U');
        $checkDay = (new \DateTime())->format('Y-m-d');

        // Get all unfinished tasks from ended sprints and get all future sprints on project
        /**
         * @var BrunoInterface[] $sprints
         */
        foreach ($sprints as $sprint) {
            $sprintAttributes = $sprint->getAttributes();
            $sprintStartDueDate =
                \DateTime::createFromFormat(
                    'U',
                    Parse::unixTimestamp($sprintAttributes['start'])
                )->format('Y-m-d');
            $sprintEndDueDate =
                \DateTime::createFromFormat(
                    'U',
                    Parse::unixTimestamp($sprintAttributes['end'])
                )->format('Y-m-d');
            if ($sprintEndDueDate < $checkDay) {
                $endedSprints[$sprintAttributes['project_id']][] = $sprint;

                // Get all tasks and check if there are unfinished tasks
                $sprintTasks = $repository->setResourceName('tasks')
                    ->loadMultiple(['sprint_id' => $sprintAttributes['_id']]);
                foreach ($sprintTasks as $task) {
                    $taskAttributes = $task->getAttributes();
                    if (isset($taskAttributes['passed_qa']) === true
                    && $taskAttributes['passed_qa'] !== true) {
                        $sprintEndedTasks[$taskAttributes['_id']] = $task;
                    }
                }
                // Check start and end due dates for future sprints
            } elseif ($unixNow < $sprintAttributes['start'] || $checkDay === $sprintStartDueDate ||
                ($unixNow > $sprintAttributes['start'] && $checkDay <= $sprintEndDueDate)
            ) {
                $futureSprints[$sprintAttributes['project_id']][] = $sprint;
                $futureSprintsStartDates[$sprintAttributes['project_id']][] = $sprintAttributes['start'];
            }
        }

        // Calculate on which projects are missing future sprints
        $missingSprints = array_diff_key($endedSprints, $futureSprints);
        $adminReport = [];

        foreach ($missingSprints as $project_id => $endedSprintsArray) {
            $adminReport[$project_id] = $activeProjects[$project_id]->getAttribute('name');
        }

        if (empty($sprintEndedTasks) === false) {
            /* Ping on slack admins if there are no future sprints created so we can move unfinished tasks from sprint to
            following sprint on sprint end date*/
            $slackService = $this->getApplication()->getService(SlackService::class);
            foreach ($adminReport as $projectName) {
                foreach ($admins as $admin) {
                    $adminAttributes = $admin->getAttributes();
                    if (isset($adminAttributes['slack'])
                        && empty($adminAttributes['slack']) === false
                        && isset($adminAttributes['active'])
                        && $adminAttributes['active'] === true
                        ) {
                        $recipient = $adminAttributes['slack'];
                        $message =
                            'Hey! There are no future sprints created to move unfinished tasks '
                            . 'from ended sprints on project : *'
                            . $projectName .
                            '*';
                        $slackService->setMessage($recipient, $message, SlackService::LOW_PRIORITY);
                    }
                }
            }

            // Move all unfinished tasks from ended sprint to following one
            foreach ($futureSprints as $projectId => $futureSprintsArray) {
                /**
                 * @var BrunoInterface[] $futureSprintsArray
                 */
                foreach ($futureSprintsArray as $futureSprint) {
                    $futureSprintAttributes = $futureSprint->getAttributes();
                    if ($futureSprintAttributes['start'] ===
                        min($futureSprintsStartDates[$futureSprintAttributes['project_id']])
                    ) {
                        foreach ($sprintEndedTasks as $task) {
                            $taskAttributes = $task->getAttributes();
                            if ($taskAttributes['project_id'] ===
                                $futureSprintAttributes['project_id']
                            ) {
                                $task->setAttribute('sprint_id', $futureSprintAttributes['_id']);
                                $task->save();
                            }
                        }
                    }
                }
            }
        }
    }
}
