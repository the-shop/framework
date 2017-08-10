<?php

namespace Modules\Http\Request;

use Modules\Application\RestApi\RestApi;

interface ApiMethodInterface
{
    public function handle();

    public function setApplication(RestApi $application);

    public function getRegisteredRequestMethods();

    public function getRegisteredRequestRoutes();
}
