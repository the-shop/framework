<?php

namespace Framework\Base\Queue\Adapters;

/**
 * Class Sync
 * @package Framework\Base\Queue\Adapters
 */
class Sync implements QueueAdapterInterface
{
    /**
     * @param string $queueName
     * @param array $payload
     * @return bool
     */
    public function handle(string $queueName = '', array $payload = [])
    {
        $taskResponse = call_user_func_array(
            [
                $payload['taskClassPath'],
                $payload['method'],
            ],
            $payload['parameters']
        );

        return $taskResponse;
    }
}
