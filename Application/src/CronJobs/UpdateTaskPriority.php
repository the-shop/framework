<?php

namespace Application\CronJobs;

use Application\CrudApi\Model\Generic;
use Application\Services\SlackService;
use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Helpers\Parse;
use Framework\Base\Model\BrunoInterface;
use Framework\Terminal\Commands\Cron\CronJob;

/**
 * Class UpdateTaskPriority
 * @package Application\CronJobs
 */
class UpdateTaskPriority extends CronJob
{
    use ApplicationAwareTrait;

    const HIGH = 'High';
    const MEDIUM = 'Medium';

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

        $tasks = $repository->setResourceName('tasks')
            ->loadMultiple();

        $unixTimeNow = (new \DateTime())->format('U');
        $unixTime7Days = (new \DateTime())->modify('+7 days')->format('U');
        $unixTime14Days = (new \DateTime())->modify('+14 days')->format('U');

        $tasksBumpedPerProject = [];

        foreach ($tasks as $task) {
            $taskAttributes = $task->getAttributes();
            if (empty($taskAttributes['owner']) === true) {
                $taskDueDate = Parse::unixTimestamp($taskAttributes['due_date']);
                // Check if task due_date is in next 7 days and switch task priority to High if not set already
                if ($taskDueDate >= $unixTimeNow
                    && $taskDueDate <= $unixTime7Days
                    && $taskAttributes['priority'] !== self::HIGH
                ) {
                    $task->setAttribute('priority', self::HIGH);
                    $task->save();
                    if (array_key_exists($taskAttributes['project_id'], $tasksBumpedPerProject)
                        === false
                    ) {
                        $tasksBumpedPerProject[$taskAttributes['project_id']]['High'] = 1;
                        $tasksBumpedPerProject[$taskAttributes['project_id']]['Medium'] = 0;
                    } else {
                        $tasksBumpedPerProject[$taskAttributes['project_id']]['High']++;
                    }
                }
                /* Check if task due_date is between next 8 - 14 days and switch task priority to Medium if not set
                 already*/
                if ($taskDueDate > $unixTime7Days
                    && $taskDueDate <= $unixTime14Days
                    && $taskAttributes['priority'] !== self::MEDIUM
                ) {
                    $task->setAttribute('priority', self::MEDIUM);
                    $task->save();
                    if (array_key_exists($taskAttributes['project_id'], $tasksBumpedPerProject)
                        === false
                    ) {
                        $tasksBumpedPerProject[$taskAttributes['project_id']]['High'] = 0;
                        $tasksBumpedPerProject[$taskAttributes['project_id']]['Medium'] = 1;
                    } else {
                        $tasksBumpedPerProject[$taskAttributes['project_id']]['Medium']++;
                    }
                }
            }
        }

        $projectOwnerIds = [];
        $projects = [];

        // Get all tasks projects and project owner IDs
        foreach ($tasksBumpedPerProject as $projectId => $count) {
            $project = $repository->setResourceName('projects')
                ->loadOne($projectId);
            $projects[$projectId] = $project;
            $acceptedBy = $project->getAttribute('acceptedBy');
            if (empty($acceptedBy) === false) {
                $projectOwnerIds[] = $acceptedBy;
            }
        }

        $recipients = $repository->setResourceName('users')
            ->loadMultiple();

        // send slack notification to all admins and POs about task priority change
        foreach ($recipients as $recipient) {
            $recipientAttributes = $recipient->getAttributes();
            if ((isset($recipientAttributes['admin']) === true
                && $recipientAttributes['admin'] === true)
                || in_array($recipientAttributes['_id'], $projectOwnerIds) === true
                && isset($recipientAttributes['slack']) === true
                && empty($recipientAttributes['slack']) === false
                && $recipientAttributes['active'] === true
            ) {
                /**
                 * @var BrunoInterface $projectToNotify
                 */
                foreach ($projects as $projectToNotify) {
                    $projectToNotifyAtt = $projectToNotify->getAttributes();
                    if (isset($recipientAttributes['admin']) === true
                        && $recipientAttributes['admin'] === false
                        && $recipientAttributes['_id'] !== $projectToNotifyAtt['acceptedBy']
                    ) {
                        continue;
                    }
                    $sendTo = '@' . $recipientAttributes['slack'];
                    $message =
                        'On project *'
                        . $projectToNotifyAtt['name']
                        . '*, there are *'
                        . $tasksBumpedPerProject[$projectToNotifyAtt['_id']]['High']
                        . '* tasks bumped to *High priority* '
                        . 'and *'
                        . $tasksBumpedPerProject[$projectToNotifyAtt['_id']]['Medium']
                        . '* bumped to *Medium priority*';

                    $slackService = $this->getApplication()
                        ->getService(SlackService::class);
                    $slackService->setMessage(
                        $sendTo,
                        $message,
                        SlackService::LOW_PRIORITY
                    );
                }
            }
        }
    }
}
