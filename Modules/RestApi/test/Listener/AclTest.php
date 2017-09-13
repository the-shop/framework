<?php

namespace Framework\RestApi\Test\Listener;

use Framework\Base\Test\UnitTest;
use Framework\Http\Request\Request;
use Framework\RestApi\Listener\Acl;
use Framework\RestApi\Module;
use Framework\RestApi\RestApi;

class AclTest extends UnitTest
{
    public function testAclRoutePermissionDenied()
    {
        $application = $this->setApplication(new RestApi([
            Module::class,
            DummyCrudApiModule::class,
        ]));

        $aclTestRules = [
            'admin' => [
                'routes' => [
                    'GET' => [],
                ],
            ],
        ];

        $application->setAclRules($aclTestRules);

        $request = new Request();
        $serverInformation = $_SERVER;
        $serverInformation['REQUEST_URI'] = '/test';

        $request->setServer($_SERVER);

        $aclListener = new Acl();
        var_dump($application);
        exit;
        $result = $aclListener->handle($request);
    }
}
