<?php

namespace Framework\Application\RestApi;

use Framework\Application\RestApi\Exception\ExceptionHandler;
use Framework\Base\Application\BaseApplication;
use Framework\Base\Module\BaseModule;

/**
 * Class Module
 * @package Framework\Application\RestApi
 */
class Module extends BaseModule
{
    private $config = [
        'listeners' => [
            BaseApplication::EVENT_APPLICATION_RENDER_RESPONSE_PRE =>
                \Framework\Application\RestApi\Listener\ResponseFormatter::class,
            ExceptionHandler::EVENT_EXCEPTION_HANDLER_HANDLE_PRE =>
                \Framework\Application\RestApi\Listener\ExceptionFormatter::class
        ]
    ];

    public function bootstrap()
    {
        foreach ($this->config['listeners'] as $event => $handlerClass) {
            $this->getApplication()->listen($event, $handlerClass);
        }
    }
}
