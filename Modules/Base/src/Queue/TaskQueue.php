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
        /**
         * @var QueueAdapterInterface $adapter
         */
        $adapter = new $adapterFullyQualifiedClassName();
        if ($adapter instanceof QueueAdapterInterface) {
            $adapter->handle($queueName, $payload);
        } else {
            throw new InvalidArgumentException(
                'Wrong adapter. Must be instance of QueueAdapterInterface.',
                403
            );
        }

        return true;
    }
}
