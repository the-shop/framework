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

        $authModelsConfigs = $this->getAuthenticatables();

        if (empty($authModelsConfigs) === false) {
            array_unshift(
                $this->config['routes'],
                [
                    'post',
                    '/login',
                    '\Framework\Base\Auth\Controller\AuthController::authenticate',
                ]
            );
            $application->getRepositoryManager()
                        ->addAuthenticatableModels($authModelsConfigs);
        }

        $application->getDispatcher()
                    ->addRoutes($this->config['routes']);

        $application->setAclRules($this->readJsonFile('acl'));

        $modelsConfiguration = $this->generateModelsConfiguration($this->readJsonFile('models'));

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

    /**
     * @param $fileName
     * @return mixed
     */
    private function readJsonFile($fileName)
    {
        return json_decode(file_get_contents(__DIR__ . "/config/" . $fileName . ".json"), true);
    }

    private function getAuthenticatables()
    {
        $models = [];
        $config = json_decode(file_get_contents(__DIR__ . "/config/models.json"), true);
        foreach ($config as $modelName => $params) {
            if (isset($params['authenticatable']) === true &&
                $params['authenticatable'] === true &&
                isset($params['authStrategy']) === true &&
                isset($params['credentials']) === true &&
                is_array($params['credentials']) === true
            ) {
                $models[$params['collection']] = [
                    'strategy' => $params['authStrategy'],
                    'credentials' => $params['credentials'],
                ];
            }
        }
        return $models;
    }
}
