<?php

namespace Application\Test\Application\Services;

use Application\Services\SlackApiClient;
use Application\Services\SlackService;
use Application\Test\Application\DummyCurlClient;
use Application\Test\Application\Traits\Helpers;
use Framework\Base\Test\UnitTest;

class SlackServiceTest extends UnitTest
{
    use Helpers;

    public function tearDown()
    {
        $this->purgeCollection('users');
        $this->purgeCollection('projects');
        $this->purgeCollection('tasks');
        $this->purgeCollection('sprints');
    }

    public function testServiceIsInstantiable()
    {
        $service = new SlackService();

        $this::assertInstanceOf(SlackService::class, $service);

        return $service;
    }
    /**
     * Test saving messages to DB
     *
     * @depends testServiceIsInstantiable
     * @param SlackService $service
     *
     * @return array
     */
    public function testSetMessage(SlackService $service)
    {
        $service->setApplication($this->getApplication());
        $slackUsername = $this->generateRandomString();

        $newMessage = $service->setMessage($slackUsername, 'testSetMessage');

        $modelId = $newMessage->getId();
        $route = '/api/v1/slackMessages/' . $modelId;
        $response = $this->makeHttpRequest('GET', $route);
        $loadedModel = $response->getBody();

        $this::assertEquals($slackUsername, $loadedModel->getAttribute('recipient'));
        $this::assertEquals('testSetMessage', $loadedModel->getAttribute('message'));
        $this::assertEquals(SlackService::MEDIUM_PRIORITY, $loadedModel->getAttribute('priority'));
        $this::assertEquals(false, $loadedModel->getAttribute('sent'));

        return [
            $service,
            $loadedModel,
            $slackUsername
        ];
    }

    /**
     * Tests sending messages to slack api
     *
     * @depends testSetMessage
     * @param array $input
     */
    public function testPushMessages(array $input)
    {
        $service = $input[0];
        $message = $input[1];
        $slackUsername = $input[2];
        $apiClient = new SlackApiClient();
        $apiClient->setClient(new DummyCurlClient($slackUsername))
                  ->setApplication($this->getApplication());
        $service->setApiClient($apiClient);

        $response = $service->pushMessages([$message]);

        $this::assertEquals('Message sent to ' . $slackUsername, $response['sent'][0]);
    }
}
