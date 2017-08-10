<?php

namespace Framework\Base\Module;

use Framework\Application\RestApi\RestApi;

interface ModuleInterface
{
    public function bootstrap();

    public function setApplication(RestApi $application);

    public function getApplication();
}