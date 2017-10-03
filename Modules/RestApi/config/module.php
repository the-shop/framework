<?php

use Framework\RestApi\Listener\Acl;
use Framework\RestApi\Listener\ExceptionFormatter;
use Framework\RestApi\Listener\ResponseFormatter;
use Framework\Base\Application\Exception\ExceptionHandler;
use Framework\Base\Application\BaseApplication;

return [
    'listeners' => [
        BaseApplication::EVENT_APPLICATION_RENDER_RESPONSE_PRE =>
            ResponseFormatter::class,
        ExceptionHandler::EVENT_EXCEPTION_HANDLER_HANDLE_PRE =>
            ExceptionFormatter::class,
        BaseApplication::EVENT_APPLICATION_HANDLE_REQUEST_PRE =>
            Acl::class
    ]
];
