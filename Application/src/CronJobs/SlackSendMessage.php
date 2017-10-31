<?php

namespace Application\CronJobs;

use Application\Services\SlackService;
use Framework\Terminal\Commands\Cron\CronJob;

class SlackSendMessage extends CronJob
{
    /**
     * Execute the console command.
     */
    public function execute()
    {
        // Load configuration
        $priorityMapping = $this->getApplication()
                                ->getConfiguration()
                                ->getPathValue(
                                    'internal.slack.priorityToMinutesDelay'
                                );
        $output = [];

        $unixNow = time();
        $currentMinuteUnix = $unixNow - $unixNow % 60; // First second of current minute
        $nextMinuteUnix = $currentMinuteUnix + 60; // First second of next minute

        /** @var SlackService $service */
        $service = $this->getApplication()
                        ->getService(SlackService::class);

        if ($service->getApiClient() === null) {
            $service->setApiClient();
        }

        $repository = $this->getApplication()
                           ->getRepositoryManager()
                           ->getRepositoryFromResourceName('slackMessages');

        // Load by priority
        foreach ($priorityMapping as $priority => $minutesDelay) {
            $model = $repository->newModel();
            $query = $repository->createNewQueryForModel($model)
                                ->addAndCondition('priority', '=', $priority)
                                ->addAndCondition('sent', '=', false)
                                ->addAndCondition('runAt', '<', $nextMinuteUnix);

            $messages = $repository->loadMultiple($query);

            // Push to slack via service
            $output = array_merge_recursive($output, $service->pushMessages($messages));
        }
        return $output;
    }
}
