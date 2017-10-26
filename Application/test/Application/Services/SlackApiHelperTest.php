<?php

namespace Application\Test\Application\Services;

use Application\Services\SlackApiHelper;
use Application\Test\Application\DummyCurlClient;
use Framework\Base\Application\Exception\NotFoundException;
use Framework\Base\Test\UnitTest;
use Psr\Http\Message\ResponseInterface;

class SlackApiHelperTest extends UnitTest
{
    public function testIsSlackApiHelperInstantiatable()
    {
        $client = new SlackApiHelper();
        $client->setClient(new DummyCurlClient())
               ->setApplication($this->getApplication());

        $this::assertInstanceOf(SlackApiHelper::class, $client);

        return $client;
    }

    /**
     * @param \Application\Services\SlackApiHelper $client
     *
     * @depends testIsSlackApiHelperInstantiatable
     */
    public function testSettersAndGetters(SlackApiHelper $client)
    {
        $client->addPostParam('post', 'test');
        $client->addQueryParam('query', 'test');

        $this::assertEquals(['post' => 'test'], $client->getPostParams());
        $this::assertEquals(['query' => 'test'], $client->getQueryParams());
        $this::assertEquals(['Content-Type' => 'application/x-www-form-urlencoded'], $client->getHeaders());

        $arr = [
            '1' => 1,
            '2' => 2,
        ];
        $client->setPostParams($arr);
        $client->setQueryParams($arr);

        $this::assertEquals($arr, $client->getQueryParams());
        $this::assertEquals($arr, $client->getPostParams());

        $client->resetParams();
        $arr = array_merge($client->getQueryParams(), $client->getPostParams());

        $this::assertEmpty($arr);

        return $client;
    }

    /**
     * @param \Application\Services\SlackApiHelper $client
     *
     * @depends testSettersAndGetters
     */
    public function testLists(SlackApiHelper $client)
    {
        $this->getApplication()->getConfiguration()->setPathValue('env.SLACK_TOKEN', '123456');
        $collections = [
            'conversations', 'im', 'channels', 'files', 'groups', 'reminders', 'usergroups',
            'usergroups.users',
        ];

        $this::assertInstanceOf(ResponseInterface::class, $client->lists($collections[rand(0, 7)]));

        $this::expectException(\InvalidArgumentException::class);
        $this::expectExceptionMessage('Method invalid or not implemented');

        $this::assertInstanceOf(ResponseInterface::class, $client->lists('test'));

        return $client;
    }

    public function testGetUser()
    {
        $client = new SlackApiHelper();
        $client->setClient(new DummyCurlClient())
               ->setApplication($this->getApplication());
        $this->getApplication()->getConfiguration()->setPathValue('env.SLACK_TOKEN', '123456');

        $this::assertEquals('testId', $client->getUser('test')->id);

        $this::expectException(NotFoundException::class);
        $this::expectExceptionMessage('User with that name is not found in your workspace');
        $this::expectExceptionCode(404);

        $client->getUser('not test');
    }

    public function testOpenIm()
    {
        $client = new SlackApiHelper();
        $client->setClient(new DummyCurlClient())
               ->setApplication($this->getApplication());
        $this->getApplication()->getConfiguration()->setPathValue('env.SLACK_TOKEN', '123456');

        $this::assertEquals('testChannelId', $client->openIm('testId'));

        $this::expectException(\RuntimeException::class);
        $this::expectExceptionMessage('Could not open a direct message channel with slack user id test');

        $this::assertEquals('testChannelId', $client->openIm('test'));
    }

    public function testSendMessage()
    {
        $client = new SlackApiHelper();
        $client->setClient(new DummyCurlClient())
               ->setApplication($this->getApplication());
        $this->getApplication()->getConfiguration()->setPathValue('env.SLACK_TOKEN', '123456');

        $response = $client->sendMessage('testId', 'message');

        $this::assertInstanceOf(ResponseInterface::class, $response);
        $this::assertEquals(true, json_decode($response->getBody()->getContents())->ok);

        $this::expectException(\RuntimeException::class);
        $this::expectExceptionMessage('Could not open a direct message channel with slack user id wrongId');

        $client->sendMessage('wrongId', 'message');
    }
}
