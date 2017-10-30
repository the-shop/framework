<?php

namespace Application\Test\Application;

class DummyCurlClient
{
    private $slackUsername = '';

    public function __construct(string $slackUsername = '')
    {
        $this->slackUsername = $slackUsername;
    }

    public function get(string $str = '', array $arr = [])
    {
        $response = new DummyCurlResponse();

        if ($str === 'https://slack.com/api/users.list') {
            $user = (object) [
                'name' => $this->slackUsername,
                'id' => 'testId'
            ];
            $content = (object) [
                'members' => [
                    $user
                ]
            ];
            $response->setContents(json_encode($content));
        }
        return $response;
    }

    public function post(string $str = '', array $arr = [])
    {
        $response = new DummyCurlResponse();

        if ($str === 'https://slack.com/api/im.open' && $arr['form_params']['user'] === 'testId') {
            $channel = (object) [
                'id' => 'testChannelId'
            ];
            $content = (object) [
                'channel' => $channel
            ];
            $response->setContents(json_encode($content));
        } elseif ($str === 'https://slack.com/api/chat.postMessage' &&
                  $arr['form_params']['channel'] === 'testChannelId') {
            $content = (object) [
                'ok' => true,
            ];
            $response->setContents(json_encode($content));
        }

        return $response;
    }
}
