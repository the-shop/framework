<?php

namespace Framework\Base\Logger;

/**
 * Class Replicant (Dummy Logger)
 * @package Framework\Base\Logger
 */
class DummyLogger implements LoggerInterface
{
    private $logs = [];

    public function log(LogInterface $log)
    {
        $this->logs[] = $log;

        return $this;
    }

    public function getLogs()
    {
        return $this->logs;
    }
}