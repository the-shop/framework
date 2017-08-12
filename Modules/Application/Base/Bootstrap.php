<?php

namespace Framework\Application\Base;

/**
 * Class Bootstrap
 * @package Framework\Application\Base
 */
class Bootstrap
{
    private $registerModules = [];

    /**
     * @param array $moduleInterfaceClassNames
     * @param BaseApplication $application
     * @return $this
     */
    public function registerModules(array $moduleInterfaceClassNames = [], BaseApplication $application)
    {
        $this->registerModules = $moduleInterfaceClassNames;

        foreach ($this->registerModules as $moduleClass) {
            /* @var \Framework\Base\Module\ModuleInterface $instance */
            $instance = new $moduleClass();
            $instance->setApplication($application);
            $instance->bootstrap();
        }

        return $this;
    }
}
