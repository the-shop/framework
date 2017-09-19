<?php

namespace Framework\Base\Terminal;

use Framework\Base\Application\ApplicationConfiguration;
use Framework\Base\Application\BaseApplication;
use Framework\Base\Terminal\Router\Dispatcher;

/**
 * Class TerminalApp
 * @package Framework\Base\TerminalApp
 */
class TerminalApp extends BaseApplication
{
    /**
     * TerminalApp constructor.
     * @param ApplicationConfiguration|null $applicationConfiguration
     */
    public function __construct(ApplicationConfiguration $applicationConfiguration = null)
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
