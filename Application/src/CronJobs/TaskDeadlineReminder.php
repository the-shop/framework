<?php

namespace Application\CronJobs;

use Application\CrudApi\Model\Generic;
use Application\Services\SlackService;
use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Model\BrunoInterface;
use Framework\Terminal\Commands\Cron\CronJob;

/**
 * Class NotifyProjectParticipantsAboutTaskDeadline
 * @package App\Console\Commands
 */
class TaskDeadlineReminder extends CronJob
{
    use ApplicationAwareTrait;

    const DUE_DATE_PASSED = 'due_date_passed';
    const DUE_DATE_SOON = 'due_date_soon';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function execute()
    {
        $unixNow = (int)(new \DateTime())->format('U');
        // Unix timestamp 1 day before now at the beginning of the fay
        $unixYesterday = (int)(new \DateTime())->modify('-1 day')
            ->setTime(0, 0, 0)
            ->format('U');
        // Unix timestamp 7 days from now at the end of the day
        $unixSevenDaysFromNow = (int)(new \DateTime())->modify('+7 days')
            ->setTime(23, 59, 59)
            ->format('U');

        $repository = $this->getApplication()
            ->getRepositoryManager()
            ->getRepository(Generic::class);

        $tasksQuery = $repository->setResourceName('tasks')
            ->createNewQueryForModel($repository->newModel());
        // Get all unfinished tasks with due_date between yesterday and next 7 days
        $tasksQuery->addAndCondition('due_date', '<=', $unixSevenDaysFromNow)
            ->addAndCondition('due_date', '>=', $unixYesterday)
            ->addAndCondition('ready', '=', true)
            ->addAndCondition('passed_qa', '=', false);

        $tasks = $repository->loadMultiple($tasksQuery);

        $projects = [];
        $tasksDueDatePassed = [];
        $tasksDueDateIn7Days = [];

        foreach ($tasks as $task) {
            $taskAttributes = $task->getAttributes();
            if (array_key_exists($taskAttributes['project_id'], $projects) !== true) {
                $project = $repository->setResourceName('projects')
                    ->loadOne($taskAttributes['project_id']);

                if ($project) {
                    $projects[$project->getAttribute('_id')] = $project;
                }
            }
            if ($taskAttributes['due_date'] <= $unixNow) {
                $tasksDueDatePassed[$taskAttributes['due_date']][] = $task;
            } else {
                $tasksDueDateIn7Days[$taskAttributes['due_date']][] = $task;
            }
        }

        // Sort array of tasks ascending by due_date so we can notify about deadline
        ksort($tasksDueDateIn7Days);

        $profiles = $repository->setResourceName('users')->loadMultiple(['active' => true]);

        foreach ($profiles as $recipient) {
            $recipientAttributes = $recipient->getAttributes();
            if (array_key_exists('slack', $recipientAttributes) === true
                && empty($recipientAttributes['slack']) === false) {
                $recipientSlack = '@' . $recipientAttributes['slack'];

                /*Loop through tasks that have due_date within next 7 days, compare skills with
                recipient skills and get max 3 tasks with nearest due_date*/
                $tasksToNotifyRecipient = [];
                foreach ($tasksDueDateIn7Days as $tasksToNotifyArray) {
                    /**
                     * @var BrunoInterface $taskToNotify
                     */
                    foreach ($tasksToNotifyArray as $taskToNotify) {
                        $taskToNotifyAttributes = $taskToNotify->getAttributes();

                        if (isset($recipientAttributes['admin']) === true
                            && $recipientAttributes['admin'] === false
                            && $recipientAttributes['_id'] !==
                            $projects[$taskToNotifyAttributes['project_id']]
                                ->getAttribute('acceptedBy')
                            && in_array(
                                $recipientAttributes['_id'],
                                $projects[$taskToNotifyAttributes['project_id']]
                                    ->getAttribute('members')
                            ) === false
                        ) {
                            continue;
                        }

                        $compareSkills = array_intersect(
                            $recipientAttributes['skills'],
                            $taskToNotifyAttributes['skillset']
                        );

                        if (empty($compareSkills) === false && count($tasksToNotifyRecipient) < 3) {
                            $tasksToNotifyRecipient[] = $taskToNotify;
                        }
                    }
                }

                /* Look if there are some tasks with due_date passed within project where recipient is PO*/
                $tasksToNotifyPo = [];
                foreach ($tasksDueDatePassed as $dueDateTasksArray) {
                    /**
                     * @var BrunoInterface $taskPassed
                     */
                    foreach ($dueDateTasksArray as $taskPassed) {
                        if ($recipientAttributes['_id']
                            === $projects[$taskPassed->getAttribute('project_id')]->getAttribute('acceptedBy')
                        ) {
                            $tasksToNotifyPo[] = $taskPassed;
                        }
                    }
                }

                /**
                 * @var SlackService $slackService
                 */
                $slackService = $this->getApplication()->getService(SlackService::class);

                // Create message for tasks with due_date within next 7 days
                $messageDeadlineSoon = $this->createMessage(
                    self::DUE_DATE_SOON,
                    $tasksToNotifyRecipient
                );
                if ($messageDeadlineSoon) {
                    $slackService->setMessage(
                        $recipientSlack,
                        $messageDeadlineSoon,
                        $private = false,
                        SlackService::LOW_PRIORITY
                    );
                }
                // Create message for tasks that due_date has passed for PO
                $messageDeadlinePassed = $this->createMessage(
                    self::DUE_DATE_PASSED,
                    $tasksToNotifyPo
                );
                if ($messageDeadlinePassed) {
                    $slackService->setMessage(
                        $recipientSlack,
                        $messageDeadlinePassed,
                        $private = false,
                        SlackService::LOW_PRIORITY
                    );
                }
            }
        }
    }

    /**
     * Helper for creating message about tasks deadline
     * @param array $tasks
     * @param $format
     * @return bool|string
     */
    private function createMessage($format, array $tasks = [])
    {
        if (empty($tasks)) {
            return false;
        }

        $webDomain = $this->getApplication()
            ->getConfiguration()
            ->getPathValue('env.WEB_DOMAIN');

        $message = '';

        if ($format === self::DUE_DATE_SOON) {
            $message = 'Hey, these tasks *due_date soon*:';
        }
        if ($format === self::DUE_DATE_PASSED) {
            $message = 'Hey, these tasks *due_date has passed*:';
        }

        foreach ($tasks as $task) {
            $taskAttributes = $task->getAttributes();
            $message .= ' *'
                . $taskAttributes['title']
                . ' ('
                . \DateTime::createFromFormat('U', $taskAttributes['due_date'])
                    ->format('Y-m-d')
                . ')* '
                . $webDomain
                . 'projects/'
                . $taskAttributes['project_id']
                . '/sprints/'
                . $taskAttributes['sprint_id']
                . '/tasks/'
                . $taskAttributes['_id']
                . ' ';
        }

        return $message;
    }
}
