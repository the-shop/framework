<?php

namespace Framework\Terminal;

use Framework\Base\Module\BaseModule;

/**
 * Class Module
 * @package Framework\Base\Terminal
 */
class Module extends BaseModule
{
    /**
     * Bootstrap this module
     */
    public function bootstrap()
    {
        $application = $this->getApplication();
        // Let's read all files from module config folder and set to Configuration
        $configDirPath = $application->getRootPath() . '/Modules/Terminal/config/';
        $this->setModuleConfiguration($configDirPath);
        $appConfig = $application->getConfiguration();

        // Add routes to dispatcher
        $application->getDispatcher()
            ->addRoutes($appConfig->getPathValue('routes'));

        $listeners = $appConfig->getPathValue('listeners');
        foreach ($listeners as $event => $arrayHandlers) {
            foreach ($arrayHandlers as $handlerClass) {
                $this->getApplication()->listen($event, $handlerClass);
            }
        }
    }
}
