<?php

namespace Framework\Base\Test\Auth\Controller;

use Firebase\JWT\JWT;
use Framework\Base\Test\TestDatabaseAdapter;
use Framework\Base\Test\TestModel;
use Framework\Base\Test\TestRepository;
use Framework\Base\Test\UnitTest;
use Framework\Http\Response\Response;

class AuthControllerTest extends UnitTest
{
    public function testAuthenticationSuccess()
    {
        $this->loadTestClasses();

        $repository = $this->getApplication()
                           ->getRepositoryManager()
                           ->getRepositoryFromResourceName('tests');

        $model = new TestModel();

        $model->defineModelAttributes($this->getFields()['tests']);
        $model->setApplication($this->getApplication());
        $model->setAttribute('email', 'test@test.com');
        $model->setAttribute('password', password_hash('test123', PASSWORD_BCRYPT));

        $repository->getPrimaryAdapter()->setLoadOneResult($model);
        $post = [
            'email' => 'test@test.com',
            'password' => 'test123',
        ];

        $response = $this->makeHttpRequest('POST', '/login', $post);

        $this::assertInstanceOf(Response::class, $response);
        $this::assertInternalType('string', $response->getBody());
        $this::assertInstanceOf(
            \stdClass::class,
            JWT::decode(
                $response->getBody(),
                'rV)7Djb{DpEpY5ex',
                ['HS384']
            )
        );
    }

    /**
     * Test Auth strategy not registered/matched, strategy not registered
     */
    public function testAuthenticationFail()
    {
        $this->loadTestClasses();
        $repository = $this->getApplication()
                           ->getRepositoryManager()
                           ->getRepositoryFromResourceName('tests');

        $model = new TestModel();

        $model->defineModelAttributes($this->getFields()['tests']);
        $model->setApplication($this->getApplication());
        $model->setAttribute('email', 'test@test.com');
        $model->setAttribute('password', password_hash('test123', PASSWORD_BCRYPT));

        $repository->getPrimaryAdapter()->setLoadOneResult($model);
        $post = [
            'email' => 'test@test.com',
            'password' => 'test1233',
            'oneTooMany' => 'fail',
        ];

        $response = $this->makeHttpRequest('POST', '/login', $post);

        $this::assertEquals('Auth strategy not registered', $response->getBody()['errors'][0]);
        $this::assertEquals(500, $response->getCode());

        $authModel['tests'] = ['strategy' => 'Imagined', 'credentials' => ['email', 'password', 'oneTooMany']];

        $this->getApplication()
             ->getRepositoryManager()
             ->addAuthenticatableModels($authModel);

        $response = $this->makeHttpRequest('POST', '/login', $post);

        $this::assertEquals('Strategy not implemented', $response->getBody()['errors'][0]);
        $this::assertEquals(500, $response->getCode());
    }
}
