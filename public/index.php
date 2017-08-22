<?php

/**
 * Require composer dependencies
 */
require_once '../vendor/autoload.php';

$app = new \Framework\RestApi\RestApi([
    \Framework\RestApi\Module::class,
    \Framework\GenericCrud\Module::class
]);

$app->run();
