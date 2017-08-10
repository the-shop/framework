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


class Bootstrap
{
    private $application = null;

    private $registerModules = [];

    public function __construct()
    {
        /**
         * Require composer dependencies
         */
        require_once 'vendor/autoload.php';
    }

    public function registerModules(array $moduleInterfaceClassNames = []) {
        $this->registerModules = $moduleInterfaceClassNames;
    }

    public function setApplication(\Framework\Application\RestApi\RestApi $application)
    {
        $this->application = $application;
    }

    public function setup()
    {
        foreach ($this->registerModules as $moduleClass) {
            /* @var \Framework\Base\Module\ModuleInterface $instance */
            $instance = new $moduleClass();
            $instance->setApplication($this->application);
            $instance->bootstrap();
        }
    }
}
