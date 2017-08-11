<?php

namespace Framework\Base\Module;

use Framework\Application\RestApi\ApplicationAwareTrait;

/**
 * Class BaseModule
 * @package Framework\Base\Module
 */
abstract class BaseModule implements ModuleInterface
{
    use ApplicationAwareTrait;
}