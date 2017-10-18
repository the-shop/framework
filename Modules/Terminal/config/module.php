<?php

use Framework\Base\Application\BaseApplication;
use Framework\Base\Application\Exception\ExceptionHandler;
use Framework\Terminal\Commands\CronJob;
use Framework\Terminal\Commands\QueueWorker;
use Framework\RestApi\Listener\ExceptionFormatter;
use Framework\RestApi\Listener\ResponseFormatter;
use Application\Database\Seeders\DatabaseSeeder;

return [
    'routes' => [
        'cron:job' => [
            'handler' => CronJob::class,
            'requiredParams' => [],
            'optionalParams' => [],
        ],
        'queue:worker' => [
            'handler' => QueueWorker::class,
            'requiredParams' => [],
            'optionalParams' => [],
        ],
        'db:seed' => [
            'handler' => DatabaseSeeder::class,
            'requiredParams' => [
                'seederName'
            ],
            'optionalParams' => [],
        ]
    ],
    'listeners' => [
        BaseApplication::EVENT_APPLICATION_RENDER_RESPONSE_PRE => [
            ResponseFormatter::class,
        ],
        ExceptionHandler::EVENT_EXCEPTION_HANDLER_HANDLE_PRE => [
            ExceptionFormatter::class,
        ]
    ],
    'queueNames' => [],
];
