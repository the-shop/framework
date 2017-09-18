<?php

namespace ApplicationTest\Application\CrudApi\Controller;

use Application\CrudApi\Model\Generic;
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
                'POST' => [],
                'PUT' => [],
            ],
            'private' => [
                'GET' => [
                    [
                        'route' => '/{resourceName}/{identifier}',
                        'allows' => [
                            'admin',
                        ],
                    ],
                ],
                'POST' => [
                    [
                        'route' => '/{resourceName}',
                        'allows' => [
                            'admin',
                        ],
                    ],
                ],
                'PUT' => [
                    [
                        'route' => '/{resourceName}/{identifier}',
                        'allows' => [
                            'admin',
                        ],
                    ],
                ],
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
                'email' => 'test@test.com',
            ]
        );

        $createdModel = $response->getBody();

        $modelAttributes = $createdModel->getAttributes();

        $this->assertNotEmpty($createdModel);
        $this->assertEquals('test', $modelAttributes['name']);
        $this->assertEquals('test@test.com', $modelAttributes['email']);
        $this->assertEquals(200, $response->getCode());

        return $createdModel;
    }

    /**
     * Resource controller -  test load one model - model found
     * @depends testCreateModel
     * @param Generic $model
     */
    public function testLoadOneModel(Generic $model)
    {
        $this->getApplication()->setAclRules($this->aclRules);

        $modelId = $model->getId();

        $route = '/users/' . $modelId;

        $response = $this->makeHttpRequest('GET', $route);

        $responseBody = $response->getBody();

        $modelAttributes = $responseBody->getAttributes();

        $this->assertNotEmpty($responseBody);
        $this->assertEquals('test', $modelAttributes['name']);
        $this->assertEquals('test@test.com', $modelAttributes['email']);
        $this->assertEquals($modelId, $modelAttributes['_id']);
        $this->assertEquals(200, $response->getCode());
    }

    /**
     *
     * Resource controller - test update one model - success
     * @depends testCreateModel
     * @param Generic $model
     * @return mixed
     */
    public function testUpdateOneModel(Generic $model)
    {
        $this->getApplication()->setAclRules($this->aclRules);

        $modelId = $model->getId();

        $route = '/users/' . $modelId;

        $response = $this->makeHttpRequest(
            'PUT',
            $route,
            [
                'name' => 'updated test name',
                'email' => 'updatedtest@test.com',
            ]
        );

        $updatedModel = $response->getBody();

        $modelAttributes = $updatedModel->getAttributes();

        $this->assertNotEmpty($updatedModel);
        $this->assertEquals('updated test name', $modelAttributes['name']);
        $this->assertEquals('updatedtest@test.com', $modelAttributes['email']);
        $this->assertEquals($modelId, $modelAttributes['_id']);
        $this->assertEquals(200, $response->getCode());

        return $updatedModel;
    }

    /**
     * Test resource controller - update one model - undefined attribute - fail
     * @depends testUpdateOneModel
     * @param Generic $model
     */
    public function testUpdateOneModelUndefinedAttribute(Generic $model)
    {
        $this->getApplication()->setAclRules($this->aclRules);

        $modelId = $model->getId();

        $route = '/users/' . $modelId;

        $response = $this->makeHttpRequest(
            'PUT',
            $route,
            [
                'name' => 'updated test name',
                'email' => 'updatedtest@test.com',
                'company' => 'test company',
            ]
        );

        $responseBody = $response->getBody();

        $this->assertArrayHasKey('error', $responseBody);
        $this->assertArrayHasKey('errors', $responseBody);

        $this->assertEquals(true, $responseBody['error']);
        $this->assertEquals('Property "company" not defined.', $responseBody['errors'][0]);
        $this->assertEquals(500, $response->getCode());
    }
}
