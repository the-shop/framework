<?php

namespace Framework\Base\Test\Application;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Application\ServiceInterface;

class SampleService implements ServiceInterface
{
    use ApplicationAwareTrait;

    public function getIdentifier()
    {
        return self::class;
    }
}
