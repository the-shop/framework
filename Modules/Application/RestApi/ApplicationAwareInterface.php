<?php

namespace Framework\Application\RestApi;

interface ApplicationAwareInterface
{
    public function setApplication(RestApi $application);
    public function getApplication();
}