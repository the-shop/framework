<?php

namespace Framework\Base\Test\Logger;

use Framework\Base\Logger\MemoryLogger;
use Framework\Base\Logger\Log;
use Framework\Base\Test\UnitTest;

class MemoryLoggerTest extends UnitTest
{
    public function testIsInstantiable()
    {
        $logger = new MemoryLogger();

        $this->assertInstanceOf(MemoryLogger::class, $logger);
    }

    public function testLogging()
    {
        $logger = new MemoryLogger();

        $payload = 'testPayload';
        $log = new Log($payload);

        $this->assertAttributeEmpty('logs', $logger);

        $logger->log($log);

        $this->assertNotEmpty($logger->getLogs());

        $this->assertContainsOnlyInstancesOf(Log::class, $logger->getLogs());
    }
}
