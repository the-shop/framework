<?php

namespace Framework\Base\Test\Event;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Event\ListenerInterface;

/**
 * Class TestListener
 * @package Framework\BaseTest\Event
 */
class TestListener implements ListenerInterface
{
    use ApplicationAwareTrait;

    /**
     * @param $payload
     * @return mixed
     */
    public function handle($payload)
    {
        return $payload;
    }
}
