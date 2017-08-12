<?php

namespace Framework\User\Api;

use Framework\Base\Database\MongoAdapter;
use Framework\Base\Module\BaseModule;
use Framework\User\Api\Model\User;
use Framework\User\Api\Repository\UserRepository;

/**
 * Class Api
 * @package Framework\User\Api
 */
class Module extends BaseModule
{
    private $config = [
        'routes' => [
            '/test' => \Framework\User\Api\Controller\User::class,
            '/test/:id' => \Framework\User\Api\Controller\User::class
        ],
        'repositories' => [
            User::class => UserRepository::class
        ]
    ];

    /**
     * @inheritdoc
     */
    public function bootstrap()
    {
        $application = $this->getApplication();

        $application->getRouter()
            ->registerRoutes($this->config['routes']);

        $mongoAdapter = new MongoAdapter();

        $application->getRepositoryManager()
            ->registerRepositories($this->config['repositories'])
            ->setDatabaseAdapter($mongoAdapter);
    }
}
