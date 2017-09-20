<?php

namespace Framework\Base\Test\Manager;

use Framework\Base\Test\UnitTest;

class RepositoryManagerTest extends UnitTest
{
    public function testAddingAuthenticatables()
    {
        $authModels = [
            'tests1' => [
                'strategy' => 'Password',
                'credentials' => [
                    'email',
                    'password',
                ],
            ],
            'tests2' => [
                'strategy' => 'password',
                'credentials' => [
                    'name',
                    'password',
                ],
            ],
        ];

        $manager = $this->getApplication()
                        ->getRepositoryManager();

        $manager->addAuthenticatableModels($authModels);

        $this::assertAttributeContains(
            $authModels['tests1'],
            'authenticatableModels',
            $manager
        );
        $this::assertAttributeContains(
            $authModels['tests2'],
            'authenticatableModels',
            $manager
        );
    }
}
