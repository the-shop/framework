<?php

/**
 * Require composer dependencies
 */
require_once '../vendor/autoload.php';

$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->load(realpath(dirname(__DIR__) . '/.env'));

$appConfig = new \Framework\Base\Application\ApplicationConfiguration();
$appConfig->setRegisteredModules([
    \Framework\RestApi\Module::class,
    \Application\CrudApi\Module::class
]);

$app = new \Framework\RestApi\RestApi($appConfig);

try {
    $app->run();
} catch (\Exception $e) {
    $app->getExceptionHandler()
        ->handle($e);

    $app->getRenderer()
        ->render($app->getResponse());
}
