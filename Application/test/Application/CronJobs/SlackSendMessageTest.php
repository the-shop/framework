<?php

namespace Application\Test\Application\CronJobs;

use Application\CronJobs\SlackSendMessage;
use Application\Services\SlackApiClient;
use Application\Services\SlackService;
use Application\Test\Application\DummyCurlClient;
use Application\Test\Application\Traits\Helpers;
use Framework\Base\Test\UnitTest;

class SlackSendMessageTest extends UnitTest
{
    use Helpers;

    public function setUp()
    {
        $this->purgeCollection('slackMessages');
    }

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
        $slackUsername = $this->generateRandomString();
        $apiClient->setClient(new DummyCurlClient($slackUsername))
                  ->setApplication($this->getApplication());

        /** @var SlackService $service */
        $service = $this->getApplication()
                        ->getService(SlackService::class)
                        ->setApiClient($apiClient);

        $service->setMessage($slackUsername, 'message', 0);

        $cronJob = new SlackSendMessage($arr);
        $cronJob->setApplication($this->getApplication());

        $this::assertEquals('Message sent to ' . $slackUsername, $cronJob->execute()['sent'][0]);

        $this->purgeCollection('slackMessages');
    }
}
