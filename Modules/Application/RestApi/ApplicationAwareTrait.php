<?php

namespace Framework\Application\RestApi;

use Framework\Application\Base\BaseApplication;

trait ApplicationAwareTrait
{
    private $application;

    public function setApplication(BaseApplication $application)
    {
        $this->application = $application;

        return $this;
    }

    /**
     * @return \Framework\Application\RestApi\RestApi
     */
    public function getApplication()
    {
        return $this->application;
    }
}
