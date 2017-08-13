<?php

namespace Framework\User\Api;

use Framework\Base\Database\MongoAdapter;
use Framework\Base\Module\BaseModule;
use Framework\Http\Router\Router;
use Framework\User\Api\Controller\User as UserController;
use Framework\User\Api\Model\User as UserModel;
use Framework\User\Api\Repository\UserRepository;

/**
 * Class Api
 * @package Framework\User\Api
 */
class Module extends BaseModule
{
    private $config = [
        'routes' => [
            '/test' => UserController::class,
            '/test/:id' => UserController::class
        ],
        'repositories' => [
            UserModel::class => UserRepository::class
        ]
    ];

    /**
     * @inheritdoc
     */
    public function bootstrap()
    {
        $application = $this->getApplication();

        $application->setRouterClass(Router::class);

        $application->getRouter()
            ->registerRoutes($this->config['routes']);

        $mongoAdapter = new MongoAdapter();

        $application->getRepositoryManager()
            ->registerRepositories($this->config['repositories'])
            ->setDatabaseAdapter($mongoAdapter);
    }
}
