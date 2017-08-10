<?php

require_once "../bootstrap.php";

$app = new \Modules\Application\RestApi\RestApi();
$response = $app->handle();
