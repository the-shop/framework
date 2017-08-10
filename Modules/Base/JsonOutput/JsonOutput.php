<?php

namespace Framework\Base\JsonOutput;

use Framework\Base\Module\ModuleInterface;
use Framework\Base\Module\OutputInterface;

class JsonOutput implements ModuleInterface, OutputInterface
{
    public function bootstrap()
    {
        // TODO: Implement bootstrap() method.
    }

    public function output($response)
    {
        echo json_encode($response);
    }
}