<?php

require_once '../vendor/autoload.php';

$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->load(__DIR__ . '/../.env');

$appConfig = new \Framework\Base\Application\ApplicationConfiguration();
$appConfig->setRegisteredModules([
    \Application\CrudApi\Module::class,
    \Framework\Base\Terminal\Module::class
]);

$app = new \Framework\Base\Terminal\TerminalApp($appConfig);

try {
    $app->run();
} catch (\Exception $e) {
    $app->getExceptionHandler()
        ->handle($e);

    $app->getRenderer()
        ->render($app->getResponse());
}
