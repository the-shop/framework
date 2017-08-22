<?php

namespace Framework\Base\Event;

use Framework\Base\Application\ApplicationAwareInterface;

/**
 * Interface ListenerInterface
 * @package Framework\Base\Events
 */
interface ListenerInterface extends ApplicationAwareInterface
{
    /**
     * @param $payload
     * @return mixed
     */
    public function handle($payload);
}
