<?php

/**
 * Require composer dependencies
 */
require_once '../vendor/autoload.php';

$app = new \Framework\Application\RestApi\RestApi([
    \Framework\Application\RestApi\Module::class,
    \Framework\GenericCrud\Api\Module::class
]);

$app->addLogger(new \Framework\Application\RestApi\SentryLogger());

$app->run();
