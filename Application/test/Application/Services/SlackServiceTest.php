<?php

namespace Application\Test\Application\Services;

use Application\Services\SlackService;
use Framework\Base\Test\UnitTest;

class SlackServiceTest extends UnitTest
{
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
     */
    public function testSendMessage(SlackService $service)
    {
        $service->setApplication($this->getApplication());
        $newMessage = $service->sendMessage('testUser', 'test');

        $modelId = $newMessage->getId();
        $route = '/api/v1/slackMessages/' . $modelId;
        $response = $this->makeHttpRequest('GET', $route);
        $loadedModel = $response->getBody();

        $this::assertEquals('testUser', $loadedModel->getAttribute('recipient'));
        $this::assertEquals('test', $loadedModel->getAttribute('message'));
        $this::assertEquals(SlackService::MEDIUM_PRIORITY, $loadedModel->getAttribute('priority'));
        $this::assertEquals(false, $loadedModel->getAttribute('sent'));
    }
}
