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
     * @var array
     */
    private $modulesConfiguration = [];

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

    /**
     * @param array $configMap
     * @return $this
     */
    public function setModulesConfiguration(array $configMap)
    {
        $this->modulesConfiguration = array_merge($this->modulesConfiguration, $configMap);

        return $this;
    }

    /**
     * @return array
     */
    public function getModulesConfiguration()
    {
        return $this->modulesConfiguration;
    }
}
