<?php

namespace Modules\Application\RestApi;

interface ApplicationAwareInterface
{
    public function setApplication(RestApi $application);
    public function getApplication();
}