<?php

namespace Framework\Base\Queue\Adapters;

/**
 * Interface QueueAdapterInterface
 * @package Framework\Base\Queue\Adapters
 */
interface QueueAdapterInterface
{
    /**
     * @param string $queueName
     * @param array $payload
     * @return mixed
     */
    public function handle(string $queueName, array $payload = []);
}
