<?php

namespace Framework\CrudApi;

use Framework\Base\Database\MongoAdapter;
use Framework\Base\Module\BaseModule;
use Framework\CrudApi\Model\Generic as GenericModel;
use Framework\CrudApi\Repository\GenericRepository;

/**
 * Class Api
 * @package Framework\CrudApi
 */
class Module extends BaseModule
{
    private $config = [
        'routes' => [
            [
                'get',
                '/{resourceName}',
                '\Framework\CrudApi\Controller\Resource::loadAll'
            ],
            [
                'get',
                '/{resourceName}/{identifier}',
                '\Framework\CrudApi\Controller\Resource::load'
            ],
            [
                'post',
                '/{resourceName}',
                '\Framework\CrudApi\Controller\Resource::create'
            ],
            [
                'put',
                '/{resourceName}/{identifier}',
                '\Framework\CrudApi\Controller\Resource::update'
            ],
            [
                'patch',
                '/{resourceName}/{identifier}',
                '\Framework\CrudApi\Controller\Resource::partialUpdate'
            ],
            [
                'delete',
                '/{resourceName}/{identifier}',
                '\Framework\CrudApi\Controller\Resource::delete'
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
