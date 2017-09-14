<?php

namespace Framework\RestApi;

use Framework\Base\Application\Exception\ExceptionHandler;
use Framework\Base\Application\BaseApplication;
use Framework\Base\Module\BaseModule;
use Framework\RestApi\Listener\Acl;
use Framework\RestApi\Listener\ExceptionFormatter;
use Framework\RestApi\Listener\ResponseFormatter;

/**
 * Class Module
 * @package Framework\RestApi
 */
class Module extends BaseModule
{
    private $config = [
        'listeners' => [
            BaseApplication::EVENT_APPLICATION_RENDER_RESPONSE_PRE =>
                ResponseFormatter::class,
            ExceptionHandler::EVENT_EXCEPTION_HANDLER_HANDLE_PRE =>
                ExceptionFormatter::class,
            BaseApplication::EVENT_APPLICATION_HANDLE_REQUEST_PRE =>
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
