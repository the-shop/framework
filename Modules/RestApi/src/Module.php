<?php

namespace Framework\RestApi;

use Framework\Base\Application\Exception\ExceptionHandler;
use Framework\Base\Application\BaseApplication;
use Framework\Base\Module\BaseModule;

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
                \Framework\RestApi\Listener\ExceptionFormatter::class
        ]
    ];

    public function bootstrap()
    {
        foreach ($this->config['listeners'] as $event => $handlerClass) {
            $this->getApplication()->listen($event, $handlerClass);
        }
    }
}
