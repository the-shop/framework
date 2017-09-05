<?php

namespace Framework\BaseTest\Logger;

use Framework\Base\Logger\DummyLogger;
use Framework\Base\Logger\Log;
use Framework\Base\Test\UnitTest;

class DummyLoggerTest extends UnitTest
{
    public function testIsInstantiable()
    {
        $logger = new DummyLogger();

        $this->assertInstanceOf(DummyLogger::class, $logger);
    }

    public function testLogging()
    {
        $logger = new DummyLogger();

        $payload = 'testPayload';
        $log = new Log($payload);

        $this->assertAttributeEmpty('logs', $logger);

        $logger->log($log);

        $this->assertNotEmpty($logger->getLogs());

        $this->assertContainsOnlyInstancesOf(Log::class, $logger->getLogs());
    }
}
