<?php

namespace Framework\RestApi\Test\Listener;

use Framework\Base\Application\Exception\MethodNotAllowedException;
use Framework\Base\Test\UnitTest;
use Framework\Http\Request\Request;
use Framework\RestApi\Listener\Acl;
use Framework\RestApi\Module;
use Framework\RestApi\RestApi;

/**
 * Class AclTest
 * @package Framework\RestApi\Test\Listener
 */
class AclTest extends UnitTest
{
    const EVENT_CRUD_API_RESOURCE_LOAD_ALL_PRE = 'EVENT\CRUD_API\RESOURCE_LOAD_ALL_PRE';

    /**
     * Test acl listener for routes, user has got no permission for requested route - exception
     */
    public function testAclRoutePermissionDenied()
    {
        $application = $this->setApplication(new RestApi([
            Module::class,
            DummyCrudApiModule::class,
        ]));

        $aclTestRules = [
            'routes' => [
                'public' => [
                    'GET' => [],
                ],
                'private' => [
                    'GET' => [],
                ],
            ],
        ];

        $application->setAclRules($aclTestRules);

        $request = new Request();
        $serverInformation = $_SERVER;
        $serverInformation['REQUEST_URI'] = '/test';
        $serverInformation['REQUEST_METHOD'] = 'GET';

        $request->setServer($serverInformation);

        $this->expectException(MethodNotAllowedException::class);
        $this->expectExceptionCode(403);

        $application->triggerEvent(
            self::EVENT_CRUD_API_RESOURCE_LOAD_ALL_PRE,
            $request
        );
    }

    /**
     * Test acl listener for routes, user has got permission - allowed to visit requested route
     */
    public function testAclRuleAllowed()
    {
        $application = $this->setApplication(new RestApi([
            Module::class,
            DummyCrudApiModule::class,
        ]));

        $aclTestRules = [
            'routes' => [
                'public' => [
                    'GET' => [],
                ],
                'private' => [
                    'GET' => [
                        [
                            'route' => '/test',
                            'allows' => [
                                'admin'
                            ]
                        ]
                    ],
                ],
            ],
        ];

        $application->setAclRules($aclTestRules);

        $request = new Request();
        $serverInformation = $_SERVER;
        $serverInformation['REQUEST_URI'] = '/test';
        $serverInformation['REQUEST_METHOD'] = 'GET';

        $request->setServer($serverInformation);

        $out = $application->triggerEvent(
            self::EVENT_CRUD_API_RESOURCE_LOAD_ALL_PRE,
            $request
        );

        $this->assertInstanceOf(Acl::class, $out[0]);
    }
}
