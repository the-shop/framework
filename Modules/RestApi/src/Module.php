<?php

namespace Framework\RestApi;

use Application\CrudApi\Controller\Resource;
use Framework\Base\Application\Exception\ExceptionHandler;
use Framework\Base\Application\BaseApplication;
use Framework\Base\Module\BaseModule;
use Framework\RestApi\Listener\Acl;

/**
 * Class Module
 * @package Framework\RestApi
 */
class Module extends BaseModule
{
    private $config = [
        'listeners' => [
            BaseApplication::EVENT_APPLICATION_RENDER_RESPONSE_PRE =>
                \Framework\RestApi\Listener\ResponseFormatter::class,
            ExceptionHandler::EVENT_EXCEPTION_HANDLER_HANDLE_PRE =>
                \Framework\RestApi\Listener\ExceptionFormatter::class,
            Resource::EVENT_CRUD_API_RESOURCE_LOAD_ALL_PRE =>
            Acl::class
        ]
    ];

    public function bootstrap()
    {
        foreach ($this->config['listeners'] as $event => $handlerClass) {
            $this->getApplication()->listen($event, $handlerClass);
        }
    }
}
