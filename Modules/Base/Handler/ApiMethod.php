<?php

namespace Framework\Base\Handler;

use Framework\Application\RestApi\ApplicationAwareInterface;
use Framework\Application\RestApi\ApplicationAwareTrait;
use Framework\Http\Request\ApiMethodInterface;

abstract class ApiMethod implements ApiMethodInterface, ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    public function getRegisteredRequestMethods()
    {
        return array_keys($this->getRegisteredRequestRoutes());
    }

    public function handle()
    {
        $requestMethod = $this->getApplication()
            ->getRequest()
            ->getMethod();

        return call_user_func([$this, $this->getRegisteredRequestRoutes()[$requestMethod]]);
    }
}