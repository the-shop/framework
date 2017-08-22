<?php

namespace Framework\Base\Logger;

interface LoggerInterface
{
    /**
     * @param LogInterface $log
     * @return mixed
     */
    public function log(LogInterface $log);
}
