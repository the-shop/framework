<?php

namespace Framework\RestApi\Test\Logger;

use Framework\Base\Logger\DummyClient;
use Framework\Base\Logger\Log;
use Framework\Base\Sentry\SentryLogger;
use Framework\Base\Test\UnitTest;

/**
 * Class SentryLoggerTest
 * @package Framework\RestApiTest\Logger
 */
class SentryLoggerTest extends UnitTest
{
    /**
     * Test sentry logger log message
     */
    public function testSentryLoggerLogMessage()
    {
        $log = new Log('Test message.');
        $dsn = 'Test dsn.';
        $sentryLogger = new SentryLogger();
        $sentryLogger->setClient($dsn, DummyClient::class);

        $out = $sentryLogger->log($log);

        $this->assertEquals([
            'message' => $log->getPayload(),
            'params' => [],
            'data' => $log->getAllData()
        ], $out);
    }

    /**
     * Test sentry logger log exception
     */
    public function testSentryLoggerLogException()
    {
        $log = new Log(new \Exception('Test exception.'));
        $sentryLogger = new SentryLogger();
        $dsn = 'Test dsn.';
        $sentryLogger->setClient($dsn, DummyClient::class);

        $out = $sentryLogger->log($log);

        $this->assertEquals([
            'exception' => $log->getPayload(),
            'data' => $log->getAllData()
        ], $out);
    }
}
