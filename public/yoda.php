<?php

require_once realpath(dirname(__DIR__) . '/vendor/autoload.php');

$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->load(__DIR__ . '/../.env');

$appConfig = new \Framework\Base\Application\ApplicationConfiguration();
$appConfig->setRegisteredModules([
    \Framework\CrudApi\Module::class,
    \Framework\Terminal\Module::class
]);

$app = new \Framework\Terminal\Yoda($appConfig);

try {
    $app->run();
} catch (\Exception $e) {
    $app->getExceptionHandler()
        ->handle($e);

    $app->getRenderer()
        ->render($app->getResponse());
}
