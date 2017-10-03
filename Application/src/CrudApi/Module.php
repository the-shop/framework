<?php

namespace Application\CrudApi;

use Framework\Base\Module\BaseModule;
use Application\CrudApi\Model\Generic as GenericModel;
use Application\CrudApi\Repository\GenericRepository;
use Framework\Base\Mongo\MongoAdapter;

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
                '\Application\CrudApi\Controller\Resource::loadAll',
            ],
            [
                'get',
                '/{resourceName}/{identifier}',
                '\Application\CrudApi\Controller\Resource::load',
            ],
            [
                'post',
                '/{resourceName}',
                '\Application\CrudApi\Controller\Resource::create',
            ],
            [
                'put',
                '/{resourceName}/{identifier}',
                '\Application\CrudApi\Controller\Resource::update',
            ],
            [
                'patch',
                '/{resourceName}/{identifier}',
                '\Application\CrudApi\Controller\Resource::partialUpdate',
            ],
            [
                'delete',
                '/{resourceName}/{identifier}',
                '\Application\CrudApi\Controller\Resource::delete',
            ],
        ],
        'repositories' => [
            GenericModel::class => GenericRepository::class,
        ],
        'modelAdapters' => [
            'users' => [
                MongoAdapter::class,
            ],
        ],
        'primaryModelAdapter' => [
            'users' => MongoAdapter::class,
        ]
    ];

    /**
     * @inheritdoc
     */
    public function bootstrap()
    {
        $application = $this->getApplication();

        $configDirPath = $application->getRootPath() . '/Application/config/';
        $this->setModuleConfiguration($configDirPath);
        $appConfig = $application->getConfiguration();

        $application->getDispatcher()
                    ->addRoutes($this->config['routes']);

        $application->setAclRules($appConfig->getPathValue('acl'));

        $modelsConfiguration = $this->generateModelsConfiguration(
            $appConfig->getPathValue('models')
        );

        $repositoryManager = $application->getRepositoryManager();

        // Register model adapters
        foreach ($this->config['modelAdapters'] as $model => $adapters) {
            foreach ($adapters as $adapter) {
                $repositoryManager->addModelAdapter($model, new $adapter());
            }
        }

        // Register model primary adapters
        foreach ($this->config['primaryModelAdapter'] as $model => $primaryAdapter) {
            $repositoryManager->setPrimaryAdapter($model, new $primaryAdapter());
        }

        $repositoryManager->registerResources($modelsConfiguration['resources'])
                          ->registerRepositories($this->config['repositories'])
                          ->registerModelFields($modelsConfiguration['modelFields']);
    }

    /**
     * @param $modelsConfig
     * @return array
     */
    private function generateModelsConfiguration(array $modelsConfig)
    {
        $generatedConfiguration = [
            'resources' => [],
            'modelFields' => [],
        ];
        foreach ($modelsConfig as $modelName => $options) {
            $generatedConfiguration['resources'][$options['collection']] = GenericRepository::class;
            $generatedConfiguration['modelFields'][$options['collection']] = $options['fields'];
        }

        return $generatedConfiguration;
    }
}
