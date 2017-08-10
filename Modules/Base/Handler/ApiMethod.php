<?php

namespace Modules\Base\Handler;

use Modules\Application\RestApi\ApplicationAwareInterface;
use Modules\Application\RestApi\ApplicationAwareTrait;
use Modules\Http\Request\ApiMethodInterface;

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


        if (!method_exists($this, $this->getRegisteredRequestRoutes()[$requestMethod])) {
            // TODO: implement custom exception for this
            throw new \Exception('Not implemented');
        }

        return call_user_func([$this, $this->getRegisteredRequestRoutes()[$requestMethod]]);
    }
}