<?php

/**
 * Require composer dependencies
 */
require_once '../vendor/autoload.php';

$app = new \Framework\Application\RestApi\RestApi([
    \Framework\GenericCrud\Api\Module::class
]);

$request = $app->buildRequest();

$response = $app->handle($request);

$app->getRenderer()
    ->render($response);
