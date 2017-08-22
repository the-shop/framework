<?php

/**
 * Require composer dependencies
 */
require_once '../vendor/autoload.php';

$app = new \Framework\RestApi\RestApi([
    \Framework\RestApi\Module::class,
    \Framework\CrudApi\Module::class
]);

$app->addLogger(new \Framework\Application\RestApi\SentryLogger());

$app->run();
