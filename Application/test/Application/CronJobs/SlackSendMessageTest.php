<?php

namespace Application\Test\Application\CronJobs;

use Application\CronJobs\SlackSendMessage;
use Application\Services\SlackApiClient;
use Application\Services\SlackService;
use Application\Test\Application\DummyCurlClient;
use Framework\Base\Test\UnitTest;

class SlackSendMessageTest extends UnitTest
{
    public function testExecute()
    {
        $arr = [
            'timer' => 'everyMinute',
            'args' => [],
        ];

        $this->getApplication()
             ->getConfiguration()
             ->setPathValue('internal.slack.priorityToMinutesDelay.0', 0);

        $apiClient = new SlackApiClient();
        $apiClient->setClient(new DummyCurlClient())
                  ->setApplication($this->getApplication());

        /** @var SlackService $service */
        $service = $this->getApplication()
                        ->getService(SlackService::class)
                        ->setApiClient($apiClient);

        $service->setMessage('test', 'message', false, 0);

        $cronJob = new SlackSendMessage($arr);
        $cronJob->setApplication($this->getApplication());

        $this::assertEquals('Message sent to test', $cronJob->execute()['sent'][0]);

        $this->delete();
    }

    /**
     * Deletes test record from db
     */
    private function delete()
    {
        $repository = $this->getApplication()
                           ->getRepositoryManager()
                           ->getRepositoryFromResourceName('slackMessages');

        $model = $repository->newModel();
        $query = $repository->getPrimaryAdapter()
                            ->newQuery()
                            ->setDatabase($model->getDatabase())
                            ->setCollection('slackMessages')
                            ->addAndCondition('message', '=', 'message')
                            ->addAndCondition('recipient', '=', 'test');

        $repository->loadOne($query)->delete();
    }
}
