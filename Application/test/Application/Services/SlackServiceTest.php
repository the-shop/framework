<?php

namespace Application\Test\Application\Services;

use Application\Services\SlackApiClient;
use Application\Services\SlackService;
use Application\Test\Application\DummyCurlClient;
use Framework\Base\Test\UnitTest;
use Framework\Base\Model\BrunoInterface;

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
    public function testSetMessage(SlackService $service)
    {
        $service->setApplication($this->getApplication());
        $newMessage = $service->setMessage('test', 'testSetMessage');

        $modelId = $newMessage->getId();
        $route = '/api/v1/slackMessages/' . $modelId;
        $response = $this->makeHttpRequest('GET', $route);
        $loadedModel = $response->getBody();

        $this::assertEquals('test', $loadedModel->getAttribute('recipient'));
        $this::assertEquals('testSetMessage', $loadedModel->getAttribute('message'));
        $this::assertEquals(SlackService::MEDIUM_PRIORITY, $loadedModel->getAttribute('priority'));
        $this::assertEquals(false, $loadedModel->getAttribute('sent'));

        return [
            $service,
            $loadedModel,
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
        $apiClient = new SlackApiClient();
        $apiClient->setClient(new DummyCurlClient())
                  ->setApplication($this->getApplication());
        $service->setApiClient($apiClient);

        $response = $service->pushMessages([$message]);

        $this::assertEquals('Message sent to test', $response['sent'][0]);

        $this->delete($message);
    }

    /**
     * Deletes test record from db
     */
    private function delete(BrunoInterface $message)
    {
        $repository = $this->getApplication()
                           ->getRepositoryManager()
                           ->getRepositoryFromResourceName('slackMessages');

        $repository->loadOne($message->getId())->delete();
    }
}
