<?php

namespace Framework\Base\Queue\Adapters;

/**
 * Class Sync
 * @package Framework\Base\Queue\Adapters
 */
class Sync
{
    /**
     * @param string $queueName
     * @param array $payload
     */
    public function handle(string $queueName = '', array $payload = [])
    {
    }
}
