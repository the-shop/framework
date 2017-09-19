<?php

namespace Framework\Base\Terminal;

use Framework\Base\Application\ApplicationInterface;
use Framework\Base\Application\BaseApplication;

/**
 * Class Terminal
 * @package Framework\Base\Terminal
 */
class Terminal extends BaseApplication
{
    /**
     * Terminal constructor.
     * @param ApplicationInterface|null $applicationConfiguration
     */
    public function __construct(ApplicationInterface $applicationConfiguration = null)
    {
        parent::__construct($applicationConfiguration);
    }

    /**
     *
     */
    public function buildRequest()
    {
    }
}
