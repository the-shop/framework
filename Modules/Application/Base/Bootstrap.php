<?php

namespace Framework\Application\Base;

use Framework\Base\Di\Resolver;

class Bootstrap
{
    private $registerModules = [];

    public function registerModules(array $moduleInterfaceClassNames = [], BaseApplication $application)
    {
        $this->registerModules = $moduleInterfaceClassNames;

        foreach ($this->registerModules as $moduleClass) {
            /* @var \Framework\Base\Module\ModuleInterface $instance */
            $instance = new $moduleClass();
            $instance->setApplication($application);
            $instance->bootstrap();
        }
    }
}
