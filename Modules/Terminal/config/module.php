<?php

use Framework\Base\Application\BaseApplication;
use Framework\Base\Application\Exception\ExceptionHandler;
use Framework\Terminal\Commands\CronJob;
use Framework\Terminal\Commands\QueueWorker;
use Framework\Terminal\Commands\Test;
use Framework\RestApi\Listener\ExceptionFormatter;
use Framework\RestApi\Listener\ResponseFormatter;

return [
    'routes' => [
        'cron:job' => [
            'handler' => CronJob::class,
            'requiredParams' => [],
            'optionalParams' => [],
        ],
        'queue:worker' => [
            'handler' => QueueWorker::class,
            'requiredParams' => [
                'queueName'
            ],
            'optionalParams' => [],
        ],
        'test' => [
            'handler' => Test::class,
            'requiredParams' => [
                'testParam'
            ],
            'optionalParams' => [
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
    'queue' => [],
];
