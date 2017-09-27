<?php

namespace Framework\Base\Test\Auth;

use Framework\Base\Application\Exception\AuthenticationException;
use Framework\Base\Auth\PasswordAuthStrategy;
use Framework\Base\Test\TestDatabaseAdapter;
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
//TODO: When password auth strategy is finally completed uncomment this tests and modify them to
// work with latest bruno repository methods logic
//    public function testValidationSuccess()
//    {
//        $data = $this->getValidationTestData();
//        $repository = $data[0];
//        $authModel = $data[1];
//
//        $post = ['email' => 'test@test.com','password' => 'pw123'];
//        $strategy = new PasswordAuthStrategy($post, $repository);
//
//        $this::assertInstanceOf(
//            TestModel::class,
//            $strategy->validate($authModel['tests']['credentials'])
//        );
//    }
//
//    public function testValidationModelNotFound()
//    {
//        $data = $this->getValidationTestData();
//        $repository = $data[0];
//        $authModel = $data[1];
//
//        $post = ['email' => 'test@testic.com','password' => 'pw123'];
//        $strategy = new PasswordAuthStrategy($post, $repository);
//
//        $this::expectException(NotFoundException::class);
//        $strategy->validate($authModel['tests']['credentials']);
//    }

//    public function testValidationInvalidCredentials()
//    {
//        $data = $this->getValidationTestData();
//        $repository = $data[0];
//        $authModel = $data[1];
//
//        $post = ['email' => 'test@test.com','password' => 'pw1234'];
//        $strategy = new PasswordAuthStrategy($post, $repository);
//
//        $this::expectException(AuthenticationException::class);
//        $strategy->validate($authModel['tests']['credentials']);
//    }

    protected function getValidationTestData()
    {
        $authModel['tests'] = ['strategy' => 'Password', 'credentials' => ['email', 'password']];
        $fields['tests'] = ["email" => ["label" => "Email", "type" => "string",],
                            "password" =>["label" => "Password", "type" => "password"]];
        $repository = [TestModel::class => TestRepository::class];
        $resource = ['tests' => TestRepository::class];

        $adapter = new TestDatabaseAdapter();

        $this->getApplication()
             ->getRepositoryManager()
             ->addModelAdapter('tests', $adapter)
             ->setPrimaryAdapter('tests', $adapter)
             ->registerRepositories($repository)
             ->registerResources($resource)
             ->registerModelFields($fields)
             ->addAuthenticatableModels($authModel);


        $repository = $this->getApplication()
                           ->getRepositoryManager()
                           ->getRepositoryFromResourceName('tests');

        $model = new TestModel();

        $model->defineModelAttributes($fields['tests']);
        $model->setApplication($this->getApplication());
        $model->setAttribute('email', 'test@test.com');
        $model->setAttribute('password', password_hash('pw123', PASSWORD_BCRYPT));
        $adapter->setLoadOneResult([
            'email' => $model->getAttributes()['email'],
            'password' => $model->getAttributes()['password'],
            ]);

        return [
            $repository,
            $authModel
        ];
    }
}
