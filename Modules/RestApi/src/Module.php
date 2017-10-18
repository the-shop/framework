<?php

namespace Framework\RestApi;

use Framework\Base\Application\ApplicationInterface;
use Framework\Base\Module\BaseModule;

/**
 * Class Module
 * @package Framework\RestApi
 */
class Module extends BaseModule
{
    /**
     * @inheritdoc
    */
    public function bootstrap()
    {
        $application = $this->getApplication();

        // Let's read all files from module config folder and set to Configuration
        $configDirPath = $application->getRootPath() . '/Modules/RestApi/config/';
        $this->setModuleConfiguration($configDirPath);
        $appConfig = $application->getConfiguration();

        // Add listeners to application
        $listeners = $appConfig->getPathValue('listeners');
        foreach ($listeners as $event => $arrayHandlers) {
            foreach ($arrayHandlers as $handlerClass) {
                $application->listen($event, $handlerClass);
            }
        }

        $authModelsConfigs = $this->getAuthenticatables($application);

        if (empty($authModelsConfigs) === false) {
            $appConfig->setPathValue(
                'routes',
                [
                    [
                        'post',
                        '/login',
                        '\Framework\Base\Auth\Controller\AuthController::authenticate',
                    ],
                    [
                        'post',
                        '/forgotPassword',
                        '\Framework\Base\Auth\Controller\AuthController::forgotPassword',
                    ],
                    [
                        'post',
                        '/resetPassword',
                        '\Framework\Base\Auth\Controller\AuthController::resetPassword',
                    ],
                ]
            );

            $application->getDispatcher()
                        ->addRoutes($appConfig->getPathValue('routes'));

            $application->getRepositoryManager()
                        ->addAuthenticatableModels($authModelsConfigs);
        }
    }

    /**
     * @param \Framework\Base\Application\ApplicationInterface $application
     *
     * @return array
     */
    private function getAuthenticatables(ApplicationInterface $application): array
    {
        $models = [];

        $config = $this->readDecodedJsonFile(
            $application->getRootPath() . '/Application/config/models.json'
        );

        if ($config === null) {
            throw new \RuntimeException('Config file missing');
        }
        foreach ($config['models'] as $modelName => $params) {
            if (isset($params['authenticatable']) === true &&
                $params['authenticatable'] === true &&
                isset($params['authStrategy']) === true &&
                isset($params['credentials']) === true &&
                is_array($params['credentials']) === true &&
                isset($params['aclRoleField']) === true
            ) {
                $models[$params['collection']] = [
                    'strategy' => $params['authStrategy'],
                    'credentials' => $params['credentials'],
                    'aclRole' => $params['aclRoleField'],
                ];
            }
        }
        return $models;
    }
}
