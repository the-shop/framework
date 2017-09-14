<?php

namespace Framework\RestApi;

use Framework\Base\Application\ApplicationInterface;
use Framework\Base\Application\Exception\ExceptionHandler;
use Framework\Base\Application\BaseApplication;
use Framework\Base\Module\BaseModule;

/**
 * Class Module
 * @package Framework\RestApi
 */
class Module extends BaseModule
{
    /**
     * @var array
     */
    private $config = [
        'listeners' => [
            BaseApplication::EVENT_APPLICATION_RENDER_RESPONSE_PRE =>
                \Framework\RestApi\Listener\ResponseFormatter::class,
            ExceptionHandler::EVENT_EXCEPTION_HANDLER_HANDLE_PRE =>
                \Framework\RestApi\Listener\ExceptionFormatter::class
        ]
    ];

    /**
     * @inheritdoc
    */
    public function bootstrap()
    {
        $application = $this->getApplication();
        foreach ($this->config['listeners'] as $event => $handlerClass) {
            $application->listen($event, $handlerClass);
        }

        $authModelsConfigs = $this->getAuthenticatables($application);

        if (empty($authModelsConfigs) === false) {
            $this->config['routes'] = [
                'get',
                '/login',
                '\Framework\Base\Auth\Controller\AuthController::authenticate',
            ];
            $application->getDispatcher()
                        ->addRoutes($this->config['routes'])
                        ->getRepositoryManager()
                        ->addAuthenticatableModels($authModelsConfigs);
        }
    }

    /**
     * @param \Framework\Base\Application\ApplicationInterface $application
     *
     * @return array
     */
    private function getAuthenticatables(ApplicationInterface $application)
    {
        $models = [];

        $config = $this->readDecodedJsonFile(
            $application->getRootPath() . '/Application/src/CrudApi/config/models.json'
        );

        if ($config === null) {
            throw new \RuntimeException('Config file missing');
        }
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
            if (isset($params['aclRoleField']) === true) {
                $models[$params['collection']]['aclRole'] = $params['aclRoleField'];
            }
        }
        return $models;
    }
}
