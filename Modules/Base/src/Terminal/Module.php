<?php

namespace Framework\Base\Terminal;

use Framework\Base\Application\BaseApplication;
use Framework\Base\Application\Exception\ExceptionHandler;
use Framework\Base\Module\BaseModule;
use Framework\Base\Terminal\Commands\Test;
use Framework\RestApi\Listener\ExceptionFormatter;
use Framework\RestApi\Listener\ResponseFormatter;

/**
 * Class Module
 * @package Framework\Base\Terminal
 */
class Module extends BaseModule
{
    /**
     * @var array
     */
    private $config = [
        'routes' => [
            'test' => [
                'handler' => Test::class,
                'requiredParams' => [
                    'testParam1',
                    'testParam2'
                ],
                'optionalParams' => [
                    'testing',
                    'testOptionalParam',
                ],
            ],
        ],
        'listeners' => [
            BaseApplication::EVENT_APPLICATION_RENDER_RESPONSE_PRE =>
                ResponseFormatter::class,
            ExceptionHandler::EVENT_EXCEPTION_HANDLER_HANDLE_PRE =>
                ExceptionFormatter::class,
        ],
    ];

    /**
     * Bootstrap this module
     */
    public function bootstrap()
    {
        $this->getApplication()->getDispatcher()->addRoutes($this->config['routes']);

        foreach ($this->config['listeners'] as $event => $handlerClass) {
            $this->getApplication()->listen($event, $handlerClass);
        }
    }
}
