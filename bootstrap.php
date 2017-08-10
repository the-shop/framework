<?php

/**
 * PHP-FPM doesn't have `getallheaders` function - official bug, this is a workaround
 */
if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = array ();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

/**
 * Require composer dependencies
 */
require_once 'vendor/autoload.php';

$modules = [
    "Modules/Application/RestApi/RestApi",
];

foreach ($modules as $module) {
    require_once $module . ".php";
    $moduleClass = str_replace('/', '\\', $module);
    $instance = new $moduleClass();
    $instance->bootstrap();
}

/**
 * Require entry point class for API
 */
//require_once 'Api.php';

/**
 * Load environment variables
 */
//$dotenv = new \Symfony\Component\Dotenv\Dotenv(__DIR__);
//$dotenv->load(__DIR__.'/.env');
