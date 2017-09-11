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

        $this->clearLogFile();
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

        $this->clearLogFile();
    }

    /**
     * Clear log file
     */
    private function clearLogFile()
    {
        $rootPath = str_replace('public', '', getcwd());
        $directory = getenv('FILE_LOGGER_FILE_DIR_PATH');
        $file = getenv('FILE_LOGGER_FILE_NAME');
        $fullPathToLogFileName = $rootPath . $directory . $file;

        file_put_contents($fullPathToLogFileName, '');
    }
}
