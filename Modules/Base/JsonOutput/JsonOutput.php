<?php

namespace Modules\Base\JsonOutput;

use Modules\Base\Module\ModuleInterface;
use Modules\Base\Module\OutputInterface;

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