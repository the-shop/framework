<?php

namespace Application\CrudApi;

use Framework\Base\Database\MongoAdapter;
use Framework\Base\Module\BaseModule;
use Application\CrudApi\Model\Generic as GenericModel;
use Application\CrudApi\Repository\GenericRepository;

/**
 * Class Api
 * @package Application\CrudApi
 */
class Module extends BaseModule
{
    private $config = [
        'routes' => [
            [
                'get',
                '/{resourceName}',
                '\Application\CrudApi\Controller\Resource::loadAll'
            ],
            [
                'get',
                '/{resourceName}/{identifier}',
                '\Application\CrudApi\Controller\Resource::load'
            ],
            [
                'post',
                '/{resourceName}',
                '\Application\CrudApi\Controller\Resource::create'
            ],
            [
                'put',
                '/{resourceName}/{identifier}',
                '\Application\CrudApi\Controller\Resource::update'
            ],
            [
                'patch',
                '/{resourceName}/{identifier}',
                '\Application\CrudApi\Controller\Resource::partialUpdate'
            ],
            [
                'delete',
                '/{resourceName}/{identifier}',
                '\Application\CrudApi\Controller\Resource::delete'
            ],
        ],
        'resources' => [
            'test' => GenericRepository::class
        ],
        'repositories' => [
            GenericModel::class => GenericRepository::class
        ]
    ];

    /**
     * @inheritdoc
     */
    public function bootstrap()
    {
        $application = $this->getApplication();

        $application->getDispatcher()
            ->addRoutes($this->config['routes']);

        $mongoAdapter = new MongoAdapter();

        $application->getRepositoryManager()
            ->registerResources($this->config['resources'])
            ->registerRepositories($this->config['repositories'])
            ->setDatabaseAdapter($mongoAdapter);
    }
}
