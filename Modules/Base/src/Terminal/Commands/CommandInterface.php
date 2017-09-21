<?php

namespace Framework\Base\Terminal\Commands;

use Framework\Base\Application\ApplicationAwareInterface;

interface CommandInterface extends ApplicationAwareInterface
{
    public function handle();
}
