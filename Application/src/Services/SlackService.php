<?php

namespace Application\Services;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Application\ServiceInterface;
use Framework\Base\Model\BrunoInterface;

/**
 * Class SlackService
 * @package Application\Services
 */
class SlackService implements ServiceInterface
{
    use ApplicationAwareTrait;

    const HIGH_PRIORITY = 0;

    const MEDIUM_PRIORITY = 1;

    const LOW_PRIORITY = 2;

    /**
     * Slack Api Client
     */
    private $apiClient = null;

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return self::class;
    }

    /**
     * @param null $apiClient
     *
     * @return \Application\Services\SlackService
     */
    public function setApiClient($apiClient = null): SlackService
    {
        if ($apiClient=== null) {
            $config = $this->getApplication()
                           ->getConfiguration()
                           ->getPathValue('servicesConfig.' . $this->getIdentifier());

            $apiClient = new $config['apiClient'];

            $apiClient->setClient(new $config['httpClient'])
                      ->setApplication($this->getApplication());
        }
        $this->apiClient = $apiClient;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getApiClient()
    {
        return $this->apiClient;
    }

    /**
     * Saves message to be sent to Slack to DB
     *
     * @param string $recipient
     * @param string $message
     * @param bool   $private
     * @param int    $priority
     *
     * @return \Framework\Base\Model\BrunoInterface
     */
    public function setMessage(
        string $recipient,
        string $message,
        $priority = self::MEDIUM_PRIORITY,
        $private = false
    ): BrunoInterface {

        // Load configuration
        $priorityMapping = $this->getApplication()
                                ->getConfiguration()
                                ->getPathValue(
                                    'internal.slack.priorityToMinutesDelay'
                                );

        $secondsDelay = $priorityMapping[$priority] * 60;

        $now = time();

        if ($secondsDelay === 0) {
            $runAt = $now;
        } else {
            $runAt = $now - $now % $secondsDelay + $secondsDelay;
        }

        $attributes = [
            'recipient' => $recipient,
            'message' => $message,
            'priority' => $priority,
            'private' => $private,
            'sent' => false,
            'runAt' => $runAt,
        ];

        return $this->getApplication()
                    ->getRepositoryManager()
                    ->getRepositoryFromResourceName('slackMessages')
                    ->newModel()
                    ->setAttributes($attributes)
                    ->save();
    }

    /**
     * @param array $messages
     *
     * @return array
     */
    public function pushMessages(array $messages): array
    {
        $client = $this->getApiClient();
        $output = [];

        foreach ($messages as $message) {
            $params = [
                $client->getUser($message->getAttribute('recipient'))->id,
                '',
                $message->getAttribute('message'),
            ];

            if (json_decode($message->getAttribute('message')) === null) {
                $params = [
                    $client->getUser($message->getAttribute('recipient'))->id,
                    $message->getAttribute('message'),
                ];
            }

            $response = json_decode(
                $client->sendMessage(...$params)
                       ->getBody()
                       ->getContents()
            );
            if ($response->ok === true) {
                $message->setAttribute('sent', true);
                $output['sent'][] = 'Message sent to ' . $message->getAttribute('recipient');
            } else {
                $message->setAttribute('failed', true);
                $output['failed'][] = 'Message failed to send to ' . $message->getAttribute('recipient');
            }
            $message->save();
        }
        return $output;
    }
}
