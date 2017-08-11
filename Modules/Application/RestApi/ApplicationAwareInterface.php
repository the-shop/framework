<?php

namespace Framework\Application\RestApi;

use Framework\Application\Base\BaseApplication;

interface ApplicationAwareInterface
{
    public function setApplication(BaseApplication $application);
    public function getApplication();
}