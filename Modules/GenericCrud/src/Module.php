<?php

namespace Framework\GenericCrud;

use Framework\Base\Database\MongoAdapter;
use Framework\Base\Module\BaseModule;
use Framework\GenericCrud\Model\Generic as GenericModel;
use Framework\GenericCrud\Repository\GenericRepository;

/**
 * Class Api
 * @package Framework\GenericCrud
 */
class Module extends BaseModule
{
    private $config = [
        'routes' => [
            [
                'get',
                '/{resourceName}',
                '\Framework\GenericCrud\Controller\Resource::loadAll'
            ],
            [
                'get',
                '/{resourceName}/{identifier}',
                '\Framework\GenericCrud\Controller\Resource::load'
            ],
            [
                'post',
                '/{resourceName}',
                '\Framework\GenericCrud\Controller\Resource::create'
            ],
            [
                'put',
                '/{resourceName}/{identifier}',
                '\Framework\GenericCrud\Controller\Resource::update'
            ],
            [
                'patch',
                '/{resourceName}/{identifier}',
                '\Framework\GenericCrud\Controller\Resource::partialUpdate'
            ],
            [
                'delete',
                '/{resourceName}/{identifier}',
                '\Framework\GenericCrud\Controller\Resource::delete'
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
