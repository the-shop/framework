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
    ];

    /**
     * @inheritdoc
     */
    public function bootstrap()
    {
        $application = $this->getApplication();

        $configuration = $this->generateConfigurationFromJson('models');

        $repositoryManager = $application->getRepositoryManager();
        $repositoryManager->registerResources($configuration['resources'])
                          ->registerRepositories($this->config['repositories'])
                          ->registerModelFields($configuration['modelFields']);

        foreach ($this->config['modelAdapters'] as $model => $adapters) {
            foreach ($adapters as $adapter) {
                $repositoryManager->addModelAdapter($model, new $adapter());
            }
        }
    }

    /**
     * @param $fileName
     * @return mixed
     */
    private function generateConfigurationFromJson($fileName)
    {
        $config = json_decode(file_get_contents(__DIR__ . "/config/" . $fileName . ".json"), true);
        $generatedConfiguration = [
            'resources' => [],
            'modelFields' => [],
        ];
        foreach ($config as $modelName => $options) {
            $generatedConfiguration['resources'][$options['collection']] = GenericRepository::class;
            $generatedConfiguration['modelFields'][$options['collection']] = $options['fields'];
        }

        return $generatedConfiguration;
    }
}
