<?php

namespace Framework\Base\Logger;

/**
 * Class MemoryLogger
 * @package Framework\Base\Logger
 */
class MemoryLogger implements LoggerInterface
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
