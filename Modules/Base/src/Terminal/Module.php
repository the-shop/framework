<?php

namespace Framework\Base\Terminal;

use Framework\Base\Application\Exception\ExceptionHandler;
use Framework\Base\Module\BaseModule;
use Framework\Base\Terminal\Commands\Test;
use Framework\RestApi\Listener\ExceptionFormatter;

class Module extends BaseModule
{
    private $config = [
        'routes' => [
            'test' => Test::class
        ],
        'listeners' => [
            ExceptionHandler::EVENT_EXCEPTION_HANDLER_HANDLE_PRE =>
                ExceptionFormatter::class,
        ]
    ];

    public function bootstrap()
    {
        $this->getApplication()->getDispatcher()->addRoutes($this->config['routes']);

        foreach ($this->config['listeners'] as $event => $handlerClass) {
            $this->getApplication()->listen($event, $handlerClass);
        }
    }
}
