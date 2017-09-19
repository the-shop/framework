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
    /**
     * Test acl listener for routes, user has got no permission for requested route - exception
     */
    public function testAclRoutePermissionDenied()
    {
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

        $this->getApplication()->setAclRules($aclTestRules);

        $response = $this->makeHttpRequest('GET', '/users');

        $responseBody = $response->getBody();

        $this->assertArrayHasKey('error', $responseBody);
        $this->assertArrayHasKey('errors', $responseBody);

        $this->assertEquals(true, $responseBody['error']);
        $this->assertEquals(403, $response->getCode());
    }

    /**
     * Test acl listener for routes, user has got permission - allowed to visit requested route
     */
    public function testAclRuleAllowed()
    {
        $aclTestRules = [
            'routes' => [
                'public' => [
                    'GET' => [],
                ],
                'private' => [
                    'GET' => [
                        [
                            'route' => '/{resourceName}',
                            'allows' => [
                                'admin',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->getApplication()->setAclRules($aclTestRules);

        $response = $this->makeHttpRequest('GET', '/users');

        $this->assertEquals(200, $response->getCode());
    }
}
