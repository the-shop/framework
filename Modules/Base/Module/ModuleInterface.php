<?php

namespace Framework\Base\Module;

use Framework\Application\Base\BaseApplication;

interface ModuleInterface
{
    public function bootstrap();

    public function setApplication(BaseApplication $application);

    public function getApplication();
}