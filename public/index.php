<?php

require_once "../Bootstrap.php";

$registerModules = [
    "Framework\Application\RestApi\RestApi",
    "Framework\User\Api\Api",
];

$bootstrap = new Bootstrap();
$bootstrap->registerModules($registerModules);

$app = new \Framework\Application\RestApi\RestApi();

$bootstrap->setApplication($app);
$bootstrap->setup();

$app->handle();
