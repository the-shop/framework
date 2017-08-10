<?php

namespace Framework\Http\Request;

use Framework\Application\RestApi\RestApi;

interface ApiMethodInterface
{
    public function handle();

    public function setApplication(RestApi $application);

    public function getRegisteredRequestMethods();

    public function getRegisteredRequestRoutes();
}
