<?php

namespace Framework\Base\JsonOutput;

use Framework\Application\RestApi\ApplicationAwareTrait;
use Framework\Base\Module\ModuleInterface;
use Framework\Base\Module\OutputInterface;

class JsonOutput implements ModuleInterface, OutputInterface
{
    use ApplicationAwareTrait;

    public function bootstrap()
    {
        // TODO: Implement bootstrap() method.
    }

    public function output($response)
    {
        echo json_encode($response);
    }
}