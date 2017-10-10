<?php

use Framework\RestApi\Listener\Acl;
use Framework\RestApi\Listener\ExceptionFormatter;
use Framework\RestApi\Listener\ResponseFormatter;
use Framework\Base\Application\Exception\ExceptionHandler;
use Framework\Base\Application\BaseApplication;
use Application\CrudApi\Controller\Resource;
use Framework\RestApi\Listener\ConfirmRegistration;
use Framework\RestApi\Listener\ForgotPassword;

return [
    'listeners' => [
        BaseApplication::EVENT_APPLICATION_RENDER_RESPONSE_PRE =>
            ResponseFormatter::class,
        ExceptionHandler::EVENT_EXCEPTION_HANDLER_HANDLE_PRE =>
            ExceptionFormatter::class,
        BaseApplication::EVENT_APPLICATION_HANDLE_REQUEST_PRE =>
            Acl::class,
        Resource::EVENT_CRUD_API_RESOURCE_CREATE_POST =>
        ConfirmRegistration::class,
        Resource::EVENT_CRUD_API_RESOURCE_UPDATE_POST =>
        ForgotPassword::class
    ],
    'env' => [
        'SENTRY_DSN' => getenv('SENTRY_DSN'),
        'SENDGRID_API_KEY' => getenv('SENDGRID_API_KEY'),
        'FILE_LOGGER_FILE_NAME' => getenv('FILE_LOGGER_FILE_NAME'),
        'FILE_LOGGER_FILE_DIR_PATH' => getenv('FILE_LOGGER_FILE_DIR_PATH'),
        'MUSTACHE_TEMPLATES_DIR_PATH' => getenv('MUSTACHE_TEMPLATES_DIR_PATH'),
        'MUSTACHE_FILE_EXTENSION' => getenv('MUSTACHE_FILE_EXTENSION'),
        'RABBIT_MQ_HOST' => getenv('RABBIT_MQ_HOST'),
        'RABBIT_MQ_PORT' => getenv('RABBIT_MQ_PORT'),
        'RABBIT_MQ_USER' => getenv('RABBIT_MQ_USER'),
        'RABBIT_MQ_PASSWORD' => getenv('RABBIT_MQ_PASSWORD'),
        'PRIVATE_MAIL_FROM' => getenv('PRIVATE_MAIL_FROM'),
        'PRIVATE_MAIL_NAME' => getenv('PRIVATE_MAIL_NAME'),
        'PRIVATE_MAIL_SUBJECT' => getenv('PRIVATE_MAIL_SUBJECT'),
        'WEB_DOMAIN' => getenv('WEB_DOMAIN')
    ]
];
