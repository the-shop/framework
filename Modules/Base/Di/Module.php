<?php

namespace Framework\Base\DependencyInjection;

use Framework\Base\Module\BaseModule;

class Module extends BaseModule
{
    private $registry = [];

    public function bootstrap()
    {
        parent::bootstrap();
    }

    public function register(string $identifier, $instance)
    {
        $this->registry[$identifier] = $instance;
    }
}
