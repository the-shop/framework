<?php

namespace Framework\Base\Application;

/**
 * Interface ServiceInterface
 * @package Framework\Base\Application
 */
interface ServiceInterface extends ApplicationAwareInterface
{
    public function getIdentifier();
}
