<?php

namespace Framework\Base\Module;

use \Framework\Base\Module\ModuleInterface;

class Module implements ModuleInterface
{
    public function bootstrap()
    {
        echo 'bootstrapping here';
    }
}