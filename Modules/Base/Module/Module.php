<?php

namespace Framework\Base\Module;

use Framework\Application\RestApi\ApplicationAwareTrait;

abstract class Module implements ModuleInterface
{
    use ApplicationAwareTrait;

    public function bootstrap()
    {
        echo 'bootstrapping here';
    }
}