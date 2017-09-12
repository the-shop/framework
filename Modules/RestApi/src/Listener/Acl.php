<?php

namespace Framework\RestApi\Listener;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Event\ListenerInterface;

/**
 * Class Acl
 * @package Framework\RestApi\Listener
 */
class Acl implements ListenerInterface
{
    use ApplicationAwareTrait;

    /**
     * @param $payload
     */
    public function handle($payload)
    {
    }
}
