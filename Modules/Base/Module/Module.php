<?php

namespace Modules\Base\Module;

use \Modules\Base\Module\ModuleInterface;

class Module implements ModuleInterface
{
    public function bootstrap()
    {
        echo 'bootstrapping here';
    }
}