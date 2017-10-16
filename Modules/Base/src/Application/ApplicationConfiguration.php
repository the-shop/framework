<?php

namespace Framework\Base\Application;

/**
 * Class ApplicationConfiguration
 * @package Framework\Base\Application
 */
class ApplicationConfiguration extends Configuration
{
    /**
     * @var array
     */
    private $registeredModules = [];

    /**
     * @param array $modules
     */
    public function setRegisteredModules(array $modules)
    {
        $this->registeredModules = $modules;
    }

    /**
     * @return array
     */
    public function getRegisteredModules()
    {
        return $this->registeredModules;
    }
}
