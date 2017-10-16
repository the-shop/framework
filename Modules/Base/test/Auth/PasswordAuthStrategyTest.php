<?php

namespace Framework\Base\Test\Auth;

use Framework\Base\Application\Exception\AuthenticationException;
use Framework\Base\Application\Exception\NotFoundException;
use Framework\Base\Auth\PasswordAuthStrategy;
use Framework\Base\Test\TestModel;
use Framework\Base\Test\TestRepository;
use Framework\Base\Test\UnitTest;

class PasswordAuthStrategyTest extends UnitTest
{
    public function testIsInstantiableAndSettersAndGetters()
    {
        $post = ['two', 'elements'];
        $repository = new TestRepository();

        $strategy = new PasswordAuthStrategy($post, $repository);

        $this::assertInstanceOf(PasswordAuthStrategy::class, $strategy);

        $this::assertEquals($repository, $strategy->getRepository());
        $this::assertEquals($post[0], $strategy->getId());
        $this::assertEquals($post[1], $strategy->getPassword());
    }

    public function testValidationSuccess()
    {
        $repository = $this->registerModelAndGetRepository();

        $post = ['email' => 'test@test.com', 'password' => 'pw123'];
        $strategy = new PasswordAuthStrategy($post, $repository);

        $this::assertInstanceOf(
            TestModel::class,
            $strategy->validate($this->getAuthModel()['tests']['credentials'])
        );
    }

    public function testValidationModelNotFound()
    {
        $repository = $this->registerModelAndGetRepository();
        $repository->getPrimaryAdapter()->setLoadOneResult(null);

        $post = ['email' => 'test@testic.com','password' => 'pw123'];
        $strategy = new PasswordAuthStrategy($post, $repository);

        $this::expectException(NotFoundException::class);
        $strategy->validate($this->getAuthModel()['tests']['credentials']);
    }

    public function testValidationInvalidCredentials()
    {
        $repository = $this->registerModelAndGetRepository();

        $post = ['email' => 'test@test.com','password' => 'pw1234'];
        $strategy = new PasswordAuthStrategy($post, $repository);

        $this::expectException(AuthenticationException::class);
        $strategy->validate($this->getAuthModel()['tests']['credentials']);
    }

    protected function registerModelAndGetRepository()
    {
        $this->loadTestClasses();

        $repository = $this->getApplication()
                           ->getRepositoryManager()
                           ->getRepositoryFromResourceName('tests');

        $model = new TestModel();

        $model->defineModelAttributes($this->getFields()['tests']);
        $model->setApplication($this->getApplication());
        $model->setAttribute('email', 'test@test.com');
        $model->setAttribute('password', 'pw123');

        $repository->getPrimaryAdapter()->setLoadOneResult($model);

        return $repository;
    }
}
