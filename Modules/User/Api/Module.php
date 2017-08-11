<?php

namespace Framework\User\Api;

use Framework\Base\Database\MongoAdapter;
use Framework\Base\Module\BaseModule;
use Framework\User\Api\Models\User;
use Framework\User\Api\Repositories\UserRepository;

/**
 * Class Api
 * @package Framework\User\Api
 */
class Module extends BaseModule
{
    private $config = [
        'routes' => [
            '/test' => '\Framework\User\Api\Actions\Single'
        ],
        'repositories' => [
            User::class => UserRepository::class
        ]
    ];

    public function bootstrap()
    {
        $application = $this->getApplication();

        $application->getRouter()
            ->registerRoutes($this->config['routes']);

        $application->getRepositoryManager()
            ->registerRepositories($this->config['repositories']);

        $mongoAdapter = new MongoAdapter();

        $application->getRepositoryManager()
            ->setDatabaseAdapter($mongoAdapter);
    }
}
