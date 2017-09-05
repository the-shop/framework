<?php

namespace Framework\Base\Module;

use Framework\Base\Application\ApplicationAwareTrait;

/**
 * Class BaseModule
 * @package Framework\Base\Module
 */
abstract class BaseModule implements ModuleInterface
{
    use ApplicationAwareTrait;
}
