<?php

/**
 * Require composer dependencies
 */
require_once '../vendor/autoload.php';

$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->load(__DIR__ . '/../.env');

$app = new \Framework\RestApi\RestApi([
    \Framework\RestApi\Module::class,
    \Application\CrudApi\Module::class
]);

$dsn = getenv('SENTRY_DSN');

//$app->addLogger(new \Framework\RestApi\SentryLogger($dsn));

try {
    $app->run();
} catch (\Exception $e) {
    $app->getExceptionHandler()
        ->handle($e);

    $app->getRenderer()
        ->render($app->getResponse());
}
