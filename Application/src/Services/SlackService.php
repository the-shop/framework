<?php

namespace Application\Services;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Application\ServiceInterface;

class SlackService implements ServiceInterface
{
    use ApplicationAwareTrait;

    const HIGH_PRIORITY = 0;
    const MEDIUM_PRIORITY = 1;
    const LOW_PRIORITY = 2;

    public function getIdentifier()
    {
        return self::class;
    }

    public function sendMessage($recipient, $message, $priority = self::MEDIUM_PRIORITY)
    {
        // Load configuration
        $priorityMapping = $this->getApplication()
                                ->getConfiguration()
                                ->getPathValue(
                                    'internal.slack.priorityToMinutesDelay'
                                );

        $secondsDelay = $priorityMapping[$priority] * 60;

        $now = time();

        $attributes = [
            'recipient' => $recipient,
            'message' => $message,
            'priority' => $priority,
            'sent' => false,
            'runAt' => $now - $now % $secondsDelay + $secondsDelay,
        ];

        return $this->getApplication()
                    ->getRepositoryManager()
                    ->getRepositoryFromResourceName('slackMessages')
                    ->newModel()
                    ->setAttributes($attributes)
                    ->save();
    }
}
