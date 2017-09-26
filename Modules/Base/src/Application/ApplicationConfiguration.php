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
     * @param array $configuration
     * @return $this
     */
    public function setModulesConfiguration(array $configuration)
    {
        $this->modulesConfiguration = array_merge($configuration);

        $this->modulesConfiguration = array_unique($this->modulesConfiguration);

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
