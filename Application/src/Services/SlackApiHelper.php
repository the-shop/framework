<?php

namespace Application\Services;

use Framework\Base\Application\ApplicationAwareInterface;
use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Application\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class SlackApiHelper
 * @package Application\CrudApi
 */
class SlackApiHelper implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    /**
     * @var \GuzzleHttp\Client|null
     */
    private $client = null;

    /**
     * @var string|null
     */
    private $token = null;

    /**
     * @var array
     */
    private $postParams = [];

    /**
     * @var array
     */
    private $queryParams = [];

    /**
     * @var array
     */
    private $headers = [];

    const BASE_URI = 'https://slack.com/api/';

    /**
     * Slack collections
     *
     * @var array
     */
    private static $collections = [
        'conversations', 'users', 'im', 'channels', 'files', 'groups', 'reminders', 'usergroups', 'usergroups.users',
    ];

    /**
     * SlackApiHelper constructor.
     */
    public function __construct()
    {
        $this->addHeader('Content-Type', 'application/x-www-form-urlencoded');
    }

    /**
     * @param $client
     *
     * @return \Application\Services\SlackApiHelper
     */
    public function setClient($client): SlackApiHelper
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @param array $postParams
     *
     * @return \Application\CrudApi\SlackApiHelper
     */
    public function setPostParams(array $postParams = []): SlackApiHelper
    {
        $this->postParams = $postParams;

        return $this;
    }

    /**
     * @param array $queryParams
     *
     * @return \Application\CrudApi\SlackApiHelper
     */
    public function setQueryParams(array $queryParams = []): SlackApiHelper
    {
        $this->queryParams = $queryParams;

        return $this;
    }

    /**
     * @param array $headers
     *
     * @return \Application\Services\SlackApiHelper
     */
    public function setHeaders(array $headers = []): SlackApiHelper
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param string $key
     * @param        $value
     *
     * @return \Application\CrudApi\SlackApiHelper
     */
    public function addPostParam(string $key, $value): SlackApiHelper
    {
        $this->postParams[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @param        $value
     *
     * @return \Application\CrudApi\SlackApiHelper
     */
    public function addQueryParam(string $key, $value): SlackApiHelper
    {
        $this->queryParams[$key] = $value;

        return $this;
    }

    /**
     * @param string $headerName
     * @param string $headerValue
     *
     * @return \Application\CrudApi\SlackApiHelper
     */
    public function addHeader(string $headerName, string $headerValue): SlackApiHelper
    {
        $this->headers[$headerName] = $headerValue;

        return $this;
    }

    /**
     * SLACK_TOKEN env variable
     */
    private function setToken(): void
    {
        if ($this->token === null) {
            $this->token = $this->getApplication()->getConfiguration()->getPathValue('env.SLACK_TOKEN');
        }
        $this->addQueryParam('token', $this->token);

        $this->addPostParam('token', $this->token);
    }

    /**
     * Lists slack collection depending on input parameter
     *
     * @param string $collection
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \InvalidArgumentException
     */
    public function lists(string $collection): ResponseInterface
    {
        if (in_array($collection, $this::$collections) === false) {
            throw new \InvalidArgumentException('Method invalid or not implemented');
        }
        $this->setToken();

        $response = $this->client->get($this::BASE_URI . $collection . '.list', [
            'headers' => $this->headers,
            'query' => $this->queryParams,
        ]);

        return $response;
    }

    /**
     * Gets Slack User from name
     *
     * @param string $slackName
     *
     * @return mixed
     * @throws \Framework\Base\Application\Exception\NotFoundException
     */
    public function getUser(string $slackName)
    {
        $contents = json_decode($this->lists('users')->getBody()->getContents());

        if (isset($contents->members) === true) {
            foreach ($contents->members as $user) {
                if ($slackName === $user->name) {
                    return $user;
                }
            }
        }
        throw new NotFoundException('User with that name is not found in your workspace', 404);
    }

    /**
     * @param string $slackId
     * @param string $message
     * @param string $attachments
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendMessage(string $slackId, string $message = '', string $attachments = ''): ResponseInterface
    {
        $channel = $this->openIm($slackId);

        $this->setPostParams([
            'channel' => $channel,
            'text' => $message,
            'attachments' => $attachments,
         ]);

        $this->setToken();

        $response = $this->client->post($this::BASE_URI . 'chat.postMessage', [
            'headers' => $this->headers,
            'form_params' => $this->postParams,
        ]);

        return $response;
    }

    /**
     * @param string $slackId
     *
     * @return string
     * @throws \RuntimeException
     */
    public function openIm(string $slackId): string
    {
        $this->setPostParams(['user' => $slackId]);
        $this->setToken();

        $response = json_decode(
            $this->client->post(
                $this::BASE_URI . 'im.open',
                [
                    'headers' => $this->headers,
                    'form_params' => $this->postParams,
                ]
            )
            ->getBody()
            ->getContents()
        );

        if (isset($response->channel->id) === true) {
            return $response->channel->id;
        }
        throw new \RuntimeException('Could not open a direct message channel with slack user id ' . $slackId);
    }

    /**
     * @return \Application\CrudApi\SlackApiHelper
     */
    public function resetParams(): SlackApiHelper
    {
        $this->postParams = [];

        $this->queryParams = [];

        return $this;
    }

    /**
     * @return \GuzzleHttp\Client|null
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return array
     */
    public function getPostParams(): array
    {
        return $this->postParams;
    }

    /**
     * @return array
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
