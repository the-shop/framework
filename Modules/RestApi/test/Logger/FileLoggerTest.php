<?php

namespace Framework\RestApiTest\Logger;

use Framework\Base\Logger\FileLogger;
use Framework\Base\Logger\Log;
use Framework\Base\Test\UnitTest;
use PHPUnit\Runner\Exception;

/**
 * Class FileLoggerTest
 * @package Framework\RestApiTest\Logger
 */
class FileLoggerTest extends UnitTest
{
    /**
     * Test FileLogger with simple message payload
     */
    public function testFileLoggerLogMessage()
    {
        $log = new Log('Test message');
        $logger = new FileLogger();
        $out = $logger->log($log);
        $this->assertEquals($log->getPayload(), $out);
    }

    /**
     * Test FileLogger with exception payload
     */
    public function testFileLoggerExceptionMessage()
    {
        $log = new Log(new Exception('Test exception', 403));
        $logger = new FileLogger();
        $out = $logger->log($log);
        $this->assertEquals($log->getPayload()->__toString(), $out);
    }
}
