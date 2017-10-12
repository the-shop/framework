<?php

namespace Application\Test\Application\CrudApi\Controller;

use Application\CrudApi\Controller\Resource;
use Application\CrudApi\Model\Generic;
use Framework\Base\Test\UnitTest;
use Framework\Http\Request\Request;

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
                'PATCH' => [],
                'DELETE' => [],
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
                'PATCH' => [
                    [
                        'route' => '/{resourceName}/{identifier}',
                        'allows' => [
                            'admin',
                        ],
                    ],
                ],
                'DELETE' => [
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

    /**
     * Test resource create one model - success
     */
    public function testCreateModel()
    {
        $this->getApplication()->setAclRules($this->aclRules);

        $email = $this->generateRandomEmail();

        $response = $this->makeHttpRequest(
            'POST',
            '/api/v1/users',
            [
                'name' => 'test',
                'email' => $email,
                'password' => 'test'
            ]
        );

        $createdModel = $response->getBody();

        $modelAttributes = $createdModel->getAttributes();

        $this->assertNotEmpty($createdModel);
        $this->assertEquals('test', $modelAttributes['name']);
        $this->assertEquals($email, $modelAttributes['email']);
        $this->assertEquals(200, $response->getCode());

        return $createdModel;
    }

    public function testGetAllUsers()
    {
        $response = $this->makeHttpRequest('GET', '/api/v1/users');

        $responseBody = $response->getBody();

        $this->assertNotEmpty($responseBody);
    }

    /**
     * Resource controller -  test load one model - model found
     * @depends testCreateModel
     * @param Generic $model
     * @return mixed;
     */
    public function testLoadOneModel(Generic $model)
    {
        $this->getApplication()->setAclRules($this->aclRules);

        $modelId = $model->getId();
        $att = $model->getAttributes();

        $route = '/api/v1/users/' . $modelId;

        $response = $this->makeHttpRequest('GET', $route);

        $loadedModel = $response->getBody();

        $modelAttributes = $loadedModel->getAttributes();

        $this->assertNotEmpty($loadedModel);
        $this->assertEquals('test', $modelAttributes['name']);
        $this->assertEquals($att['email'], $modelAttributes['email']);
        $this->assertEquals($modelId, $modelAttributes['_id']);
        $this->assertEquals(200, $response->getCode());

        return $loadedModel;
    }

    /**
     *
     * Resource controller - test update one model - success
     * @depends testLoadOneModel
     * @param Generic $model
     * @return mixed
     */
    public function testUpdateOneModel(Generic $model)
    {
        $this->getApplication()->setAclRules($this->aclRules);

        $modelId = $model->getId();

        $route = '/api/v1/users/' . $modelId;

        $newEmail = $this->generateRandomEmail();

        $response = $this->makeHttpRequest(
            'PUT',
            $route,
            [
                'name' => 'updated test name',
                'email' => $newEmail,
                'password' => 'test'
            ]
        );

        $updatedModel = $response->getBody();

        $modelAttributes = $updatedModel->getAttributes();

        $this->assertNotEmpty($updatedModel);
        $this->assertEquals('updated test name', $modelAttributes['name']);
        $this->assertEquals($newEmail, $modelAttributes['email']);
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
        $newEmail = $this->generateRandomEmail(20);

        $route = '/api/v1/users/' . $modelId;

        $response = $this->makeHttpRequest(
            'PUT',
            $route,
            [
                'name' => 'updated test name',
                'email' => $newEmail,
                'password' => 'test',
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

    /**
     * Test resource controller update partial one model - success
     * @depends testUpdateOneModel
     * @param Generic $model
     * @return mixed
     */
    public function testUpdatePartialOneModel(Generic $model)
    {
        $this->getApplication()->setAclRules($this->aclRules);

        $modelId = $model->getId();

        $route = '/api/v1/users/' . $modelId;

        $newEmail = $this->generateRandomEmail();

        $response = $this->makeHttpRequest(
            'PATCH',
            $route,
            [
                'name' => 'partial updated test name',
                'email' => $newEmail,
            ]
        );

        $updatedModel = $response->getBody();

        $modelAttributes = $updatedModel->getAttributes();

        $this->assertNotEmpty($updatedModel);
        $this->assertEquals('partial updated test name', $modelAttributes['name']);
        $this->assertEquals($newEmail, $modelAttributes['email']);
        $this->assertEquals($modelId, $modelAttributes['_id']);
        $this->assertEquals(200, $response->getCode());

        return $updatedModel;
    }

    /**
     * Test resource controller - update one model - undefined attribute - fail
     * @depends testUpdatePartialOneModel
     * @param Generic $model
     */
    public function testPartialUpdateOneModelUndefinedAttribute(Generic $model)
    {
        $this->getApplication()->setAclRules($this->aclRules);

        $modelId = $model->getId();
        $newEmail = $this->generateRandomEmail(20);

        $route = '/api/v1/users/' . $modelId;

        $response = $this->makeHttpRequest(
            'PATCH',
            $route,
            [
                'name' => 'partial updated test name',
                'email' => $newEmail,
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

    /**
     * Resource controller test - delete one model - successfully
     * @depends testUpdatePartialOneModel
     * @param Generic $model
     */
    public function testDeleteOneModel(Generic $model)
    {
        $this->getApplication()->setAclRules($this->aclRules);

        $modelId = $model->getId();
        $att = $model->getAttributes();

        $route = '/api/v1/users/' . $modelId;

        $response = $this->makeHttpRequest('DELETE', $route);

        $responseBody = $response->getBody();

        $modelAttributes = $responseBody->getAttributes();

        $this->assertNotEmpty($responseBody);
        $this->assertEquals('partial updated test name', $modelAttributes['name']);
        $this->assertEquals($att['email'], $modelAttributes['email']);
        $this->assertEquals($modelId, $modelAttributes['_id']);
        $this->assertEquals(200, $response->getCode());

        $loadDeletedModel = $this->getApplication()
            ->getRepositoryManager()
            ->getRepositoryFromResourceName('users')
            ->loadOne($modelId);

        $this->assertEquals(null, $loadDeletedModel);
    }

    /**
     * Test resource controller validate input method - success
     */
    public function testResourceControllerValidateInputSuccess()
    {
        $testModelDefinition = [
            'users' => [
                'name' => [
                    'type' => 'string',
                ],
                'email' => [
                    'type' => 'string',
                    'validation' => [
                        'email',
                        'string',
                    ],
                ],
            ],
        ];

        $app = $this->getApplication();
        $app->getRepositoryManager()->registerModelFields($testModelDefinition);

        // Set request and method
        $request = new Request();
        $request->setMethod('PUT');
        $app->setRequest($request);

        $resourceController = (new Resource())->setApplication($app);
        $out = $resourceController->validateInput(
            'users',
            [
                'name' => 'testing',
                'email' => 'testing@test.com',
            ]
        );

        $this->assertInstanceOf(Resource::class, $out);
    }

    /**
     * Test resource controller validate input method - failed - exception
     */
    public function testResourceControllerValidateInputFail()
    {
        $testModelDefinition = [
            'users' => [
                'name' => [
                    'type' => 'string',
                    'validation' => [
                        'string',
                    ],
                ],
                'email' => [
                    'type' => 'string',
                    'validation' => [
                        'email',
                        'string',
                    ],
                ],
            ],
        ];

        $app = $this->getApplication();
        $app->getRepositoryManager()->registerModelFields($testModelDefinition);

        // Set request and method
        $request = new Request();
        $request->setMethod('POST');
        $app->setRequest($request);

        $resourceController = (new Resource())->setApplication($app);

        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('Malformed input.');
        $this->expectException(\RuntimeException::class);

        $resourceController->validateInput(
            'users',
            [
                'name' => [],
                'email' => 'testing@test.com',
                'password' => 'test'
            ]
        );
    }

    /**
     * Validate unique validation - model found - throw exception
     */
    public function testResourceValidateInputFailUnique()
    {
        $testModelDefinition = [
            'users' => [
                '_id' => [
                    'label' => 'ID',
                    'type' => 'string',
                    'disabled' => true,
                    'required' => false,
                ],
                'name' => [
                    'type' => 'string',
                    'validation' => [
                        'string',
                    ],
                ],
                'email' => [
                    'type' => 'string',
                    'validation' => [
                        'email',
                        'string',
                        'unique',
                    ],
                ],
            ],
        ];

        $app = $this->getApplication();
        $app->getRepositoryManager()->registerModelFields($testModelDefinition);

        // Set request and method
        $request = new Request();
        $request->setMethod('PUT');
        $app->setRequest($request);

        $resourceController = (new Resource())->setApplication($app);

        $email = $this->generateRandomEmail(15);

        $model = $app->getRepositoryManager()
            ->getRepositoryFromResourceName('users')
            ->newModel();

        $model->setAttributes([
            'name' => 'testing',
            'email' => $email,
        ]);
        $model->save();


        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('Malformed input.');
        $this->expectException(\RuntimeException::class);

        $resourceController->validateInput(
            'users',
            [
                'name' => [],
                'email' => $email,
            ]
        );
    }

    /**
     * Helper method for generating random E-mail
     * @param int $length
     * @return string
     */
    private function generateRandomEmail(int $length = 10)
    {
        // Generate random email
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $email = '';

        for ($i = 0; $i < $length; $i++) {
            $email .= $characters[rand(0, $charactersLength - 1)];
        }

        $email .= '@test.com';

        return $email;
    }
}
