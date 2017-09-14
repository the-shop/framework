<?php

namespace Framework\Base\Test\Application;

use Framework\Base\Application\ServiceInterface;

class SampleService implements ServiceInterface
{
    public function getIdentifier()
    {
        return self::class;
    }
}
