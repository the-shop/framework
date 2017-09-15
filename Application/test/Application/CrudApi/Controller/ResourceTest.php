<?php

namespace ApplicationTest\Application\CrudApi\Controller;

use Framework\Base\Test\UnitTest;

/**
 * Class ResourceTest
 * @package ApplicationTest\Application\CrudApi\Controller
 */
class ResourceTest extends UnitTest
{
    /**
     * @var array
     */
    private $aclRules = [
        'routes' => [
            'public' => [
                'GET' => [],
                'POST' => []
            ],
            'private' => [
                'GET' => [
                    [
                        'route' => '/{resourceName}/{identifier}',
                        'allows' => [
                            'admin'
                        ]
                    ]
                ],
                'POST' => [
                    [
                        'route' => '/{resourceName}',
                        'allows' => [
                            'admin'
                        ]
                    ]
                ]
            ],
        ],
    ];

    public function testGetAllUser()
    {
        $response = $this->makeHttpRequest('GET', '/users');

        $responseBody = $response->getBody();

        $this->assertNotEmpty($responseBody);
    }

    /**
     * Test resource create one model - success
     */
    public function testCreateModel()
    {
        $this->getApplication()->setAclRules($this->aclRules);

        $response = $this->makeHttpRequest(
            'POST',
            '/users',
            [
                'name' => 'test',
                'email' => 'test@test.com'
            ]
        );

        $createdModel = $response->getBody();

        $modelAttributes = $createdModel->getAttributes();

        $this->assertNotEmpty($createdModel);
        $this->assertEquals('test', $modelAttributes['name']);
        $this->assertEquals('test@test.com', $modelAttributes['email']);
    }

    /**
     * Resource controller -  test load one model - model found
     */
    public function testLoadOneModel()
    {
        $this->getApplication()->setAclRules($this->aclRules);

        $model = $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('users')
            ->newModel()
            ->setAttributes([
                'name' => 'test',
                'email' => 'test@test.com',
            ])
            ->save();

        $modelId = $model->getId();

        $route = '/users/' . $modelId;

        $response = $this->makeHttpRequest('GET', $route);

        $responseBody = $response->getBody();

        $modelAttributes = $responseBody->getAttributes();

        $this->assertNotEmpty($responseBody);
        $this->assertEquals('test', $modelAttributes['name']);
        $this->assertEquals('test@test.com', $modelAttributes['email']);
        $this->assertEquals($modelId, $modelAttributes['_id']);
    }
}
