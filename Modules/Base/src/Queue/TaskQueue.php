<?php

namespace Framework\Base\Queue;

use Framework\Base\Queue\Adapters\QueueAdapterInterface;
use InvalidArgumentException;

/**
 * Class TaskQueue
 * @package Framework\Base\Queue
 */
class TaskQueue
{
    public static function addTaskToQueue(
        string $queueName,
        string $adapterFullyQualifiedClassName,
        array $payload = []
    ) {
        // Validate payload
        if (array_key_exists('taskClassPath', $payload) !== true
            || array_key_exists('method', $payload) !== true
            || array_key_exists('parameters', $payload) !== true
            || is_array($payload['parameters']) !== true
        ) {
            throw new InvalidArgumentException(
                'Invalid payload. Must be type of array with taskClassPath, method, and array of parameters.',
                403
            );
        }

        /**
         * @var QueueAdapterInterface $adapter
         */
        $adapter = new $adapterFullyQualifiedClassName();
        if ($adapter instanceof QueueAdapterInterface) {
            return $adapter->handle($queueName, $payload);
        } else {
            throw new InvalidArgumentException(
                'Wrong adapter. Must be instance of QueueAdapterInterface.',
                403
            );
        }
    }
}
