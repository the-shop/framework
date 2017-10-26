<?php

namespace Application\Test\Application\CronJobs;

use Application\CronJobs\SlackSendMessage;
use Application\Services\SlackApiHelper;
use Application\Services\SlackService;
use Application\Test\Application\DummyCurlClient;
use Framework\Base\Test\UnitTest;

class SlackSendMessageTest extends UnitTest
{
    public function testExecute()
    {
        $arr = [
            'timer' => 'everyMinute',
            'args' => [
                SlackApiHelper::class => DummyCurlClient::class
            ],
        ];

        $this->getApplication()
             ->getConfiguration()
             ->setPathValue('cronJobs.' . SlackSendMessage::class, $arr)
             ->setPathValue('env.SLACK_TOKEN', '123456')
             ->setPathValue('internal.slack.priorityToMinutesDelay.0', 0);

        /** @var SlackService $service */
        $service = $this->getApplication()->getService(SlackService::class);

        $service->sendMessage('test', 'message', false, 0);

        $cronJob = new SlackSendMessage($arr);
        $cronJob->setApplication($this->getApplication());

        $this::assertEquals('Message sent to test', $cronJob->execute()['sent'][0]);

        $this->delete();
    }

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
