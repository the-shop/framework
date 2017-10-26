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
     * @return string
     */
    public function getIdentifier()
    {
        return self::class;
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
    public function sendMessage(
        string $recipient,
        string $message,
        $private = false,
        $priority = self::MEDIUM_PRIORITY
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
}
