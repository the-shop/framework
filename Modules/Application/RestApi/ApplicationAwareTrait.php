<?php

namespace Modules\Application\RestApi;

trait ApplicationAwareTrait
{
    private $application;

    public function setApplication(RestApi $application)
    {
        $this->application = $application;

        return $this;
    }

    /**
     * @return \Modules\Application\RestApi\RestApi
     */
    public function getApplication()
    {
        return $this->application;
    }
}
