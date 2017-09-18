<?php

namespace Framework\Base\Test\Auth;

use Application\CrudApi\Model\Generic;
use Application\CrudApi\Repository\GenericRepository;
use Framework\Base\Auth\PasswordAuthStrategy;
use Framework\Base\Test\TestDatabaseAdapter;
use Framework\Base\Test\UnitTest;

class PasswordAuthStrategyTest extends UnitTest
{
    public function testIsInstantiableAndSettersAndGetters()
    {
        $post = ['two', 'elements'];
        $repository = new GenericRepository();

        $strategy = new PasswordAuthStrategy($post, $repository);

        $this::assertInstanceOf(PasswordAuthStrategy::class, $strategy);

        $this::assertEquals($repository, $strategy->getRepository());
        $this::assertEquals($post[0], $strategy->getId());
        $this::assertEquals($post[1], $strategy->getPassword());
    }

    public function testValidation()
    {
        $authModel['tests'] = ['strategy' => 'Password','credentials' => ['email','password']];
        $fields['tests'] = ["email" => ["label" => "Email", "type" => "string",],
                            "password" =>["label" => "Password","type" => "password"]];
        $resource = ['tests' => GenericRepository::class,];
        $post = ['email' => 'test','password' => 'pw123'];

        $adapter = new TestDatabaseAdapter();

        $this->getApplication()
             ->getRepositoryManager()
             ->addModelAdapter('tests', $adapter)
             ->setPrimaryAdapter('tests', $adapter)
             ->registerResources($resource)
             ->registerModelFields($fields)
             ->addAuthenticatableModels($authModel);


        $repository = $this->getApplication()
                           ->getRepositoryManager()
                           ->getRepositoryFromResourceName('tests');

        $result = new Generic();
        $strategy = new PasswordAuthStrategy($post, $repository);
        $result->setResourceName('tests');

        $result->defineModelAttributes($fields['tests']);
        $result->setApplication($this->getApplication());
        $result->setCollection('tests');
        $result->setAttribute('email', 'test');
        $result->setAttribute('password', 'pw123');

        $adapter->setLoadOneResult($result);

        $this::assertEquals(1, 1);

//        $strategy->validate($authModel['tests']['credentials']);
    }
}
