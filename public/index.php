<?php

/**
 * Require composer dependencies
 */
require_once '../vendor/autoload.php';

$app = new \Framework\Application\RestApi\RestApi();
$app->run();
