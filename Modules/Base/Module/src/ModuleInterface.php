<?php

namespace Framework\Base\Module;

use Framework\Base\Application\ApplicationInterface;

/**
 * Interface ModuleInterface
 * @package Framework\Base\Module
 */
interface ModuleInterface
{
    /**
     * Bootstrap module
     */
    public function bootstrap();

    /**
     * @param ApplicationInterface $application
     * @return mixed
     */
    public function setApplication(ApplicationInterface $application);

    /**
     * @return ApplicationInterface|null
     */
    public function getApplication();
}
