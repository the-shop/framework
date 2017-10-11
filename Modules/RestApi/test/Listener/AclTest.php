<?php

namespace Framework\RestApi\Test\Listener;

use Framework\Base\Test\UnitTest;

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

        $response = $this->makeHttpRequest('GET', '/api/v1/users');

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
                            'route' => '/api/v1/{resourceName}',
                            'allows' => [
                                'admin',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->getApplication()->setAclRules($aclTestRules);

        $response = $this->makeHttpRequest('GET', '/api/v1/users');

        $this->assertEquals(200, $response->getCode());
    }
}
