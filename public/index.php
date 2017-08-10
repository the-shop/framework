<?php

require_once "../bootstrap.php";

$app = new \Framework\Application\RestApi\RestApi();
$response = $app->handle();
