<?php

namespace Application\CronJobs;

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

        $repository = $this->getApplication()
                           ->getRepositoryManager()
                           ->getRepositoryFromResourceName('slackMessages');

        // Load by priority
        foreach ($priorityMapping as $priority => $minutesDelay) {
            $model = $repository->newModel();
            $query = $repository->getPrimaryAdapter()
                                ->newQuery()
                                ->setDatabase($model->getDatabase())
                                ->setCollection('slackMessages')
                                ->addAndCondition('priority', '=', $priority)
                                ->addAndCondition('sent', '=', false)
                                ->addAndCondition('runAt', '<', $nextMinuteUnix);

            $messages = $repository->loadMultiple($query);

            $args = $this->getArgs();

            $httpClient = reset($args);
            $handler = key($args);

            $client = new $handler;
            $client->setClient(new $httpClient);
            $client->setApplication($this->getApplication());

            foreach ($messages as $message) {
                if (json_decode($message->getAttribute('message')) === null) {
                    $response = json_decode(
                        $client->sendMessage(
                            $client->getUser($message->getAttribute('recipient'))->id,
                            $message->getAttribute('message')
                        )
                        ->getBody()
                        ->getContents()
                    );
                } else {
                    $response = json_decode(
                        $client->sendMessage(
                            $client->getUser($message->getAttribute('recipient'))->id,
                            '',
                            $message->getAttribute('message')
                        )
                        ->getBody()
                        ->getContents()
                    );
                }
                if ($response->ok === true) {
                    $message->setAttribute('sent', true);
                    $message->save();
                    $output['sent'][] = 'Message sent to ' . $message->getAttribute('recipient');
                } else {
                    $message->setAttribute('failed', true);
                    $message->save();
                    $output['failed'][] = 'Message failed to send to ' . $message->getAttribute('recipient');
                }
            }
        }
        return $output;
    }
}
