<?php

namespace Application\CronJobs;

use Application\Services\SlackService;
use Framework\Base\Model\BrunoInterface;
use Framework\Terminal\Commands\Cron\CronJob;

class AdminsQAWaitingTasks extends CronJob
{
    public function execute()
    {
        $sendMessage = false;

        $dateYesterday = (new \DateTime('now'))->modify('-1 day')->format('Y-m-d');

        // Load configuration
        $webDomain = $this->getApplication()
                          ->getConfiguration()
                          ->getPathValue('env.WEB_DOMAIN');

        $service = $this->getApplication()
                        ->getService(SlackService::class);

        if ($service->getApiClient() === null) {
            $service->setApiClient();
        }

        // Get all tasks that are submitted for QA

        $repositoryManager = $this->getApplication()
                                  ->getRepositoryManager();

        $repository = $repositoryManager->getRepositoryFromResourceName('tasks');

        $model = $repository->newModel();

        $query = $repository->createNewQueryForModel($model)
                            ->addAndCondition('submitted_for_qa', '=', true);

        $tasksInQa = $repository->loadMultiple($query);

        // Get projects and project owners
        $projects = [];
        $projectOwners = [];
        foreach ($tasksInQa as $task) {
            if (array_key_exists($task->getAttribute('project_id'), $projects) === false) {
                $project = $repositoryManager->getRepositoryFromResourceName('projects')
                                             ->loadOne($task->getAttribute('project_id'));
                if (($project instanceof BrunoInterface) === true) {
                    $projects[$project->getId()] = $project;
                }
            }
        }
        foreach ($projects as $project) {
            $user = $repositoryManager->getRepositoryFromResourceName('users')
                                      ->loadOne($project->getAttribute('acceptedBy'));
            if (($user instanceof BrunoInterface) === true) {
                $projectOwners[] = $user;
            }
        }

        /*Loop through project owners and tasks, check if there are tasks that are submitted for QA yesterday and
        create message and send to project owners*/
        foreach ($projectOwners as $projectOwner) {
            $recipient = $projectOwner->getAttribute('slack');
            if ($recipient !== null) {
                $text = 'Hey, these tasks are *submitted for QA yesterday* and waiting for review:';

                foreach ($tasksInQa as $task) {
                    if ($projectOwner->getId() ===
                        $projects[$task->getAttribute('project_id')]->getAttribute('acceptedBy')) {
                        $historyRecords = $task->getAttribute('task_history');
                        foreach ($historyRecords as $historyRecord) {
                            if ($historyRecord['status'] === 'qa_ready' &&
                                \DateTime::createFromFormat('U', $historyRecord['timestamp'])
                                         ->format('Y-m-d') === $dateYesterday
                            ) {
                                $text .= ' *'
                                         . $task->getAttribute('title')
                                         . ' ('
                                         . \DateTime::createFromFormat(
                                             'U',
                                             $task->getAttribute('due_date')
                                         )
                                         ->format('Y-m-d')
                                         . ')* '
                                         . $webDomain
                                         . 'projects/'
                                         . $task->getAttribute('project_id')
                                         . '/sprints/'
                                         . $task->getAttribute('sprint_id')
                                         . '/tasks/'
                                         . $task->getId()
                                         . ' ';
                                if ($sendMessage === false) {
                                    $sendMessage = true;
                                }
                            }
                        }
                    }
                }
                // Save message to DB
                if ($sendMessage === true) {
                    $service->setMessage($recipient, $text, false, SlackService::HIGH_PRIORITY);
                    $sendMessage = false;
                }
            }
        }
    }
}
