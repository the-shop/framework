<?php

namespace Framework\BaseTest\Application;

use Framework\Base\Logger\DummyLogger;
use Framework\Base\Logger\Log;
use Framework\Base\Logger\LoggerInterface;
use Framework\Base\Test\UnitTest;
use Framework\RestApi\SentryLogger;

class BaseApplicationTest extends UnitTest
{
    public function testLoggers()
    {
        $payload = 'testPayload';
        $log = new Log($payload);
        $application = $this->getApplication();

        $this->assertAttributeCount(0, 'loggers', $application);


        $application->log($log);

        $this->assertContainsOnlyInstancesOf(DummyLogger::class, $application->getLoggers());

        $this->assertAttributeCount(1, 'loggers', $application);


        $dsn = getenv('SENTRY_DSN');
        $application->addLogger(new SentryLogger($dsn));

        $this->assertContainsOnlyInstancesOf(LoggerInterface::class, $application->getLoggers());

        $this->assertAttributeCount(2, 'loggers', $application);
    }

    public function testGuzzleRequestException1()
    {
        $app = $this->getApplication();

        $this::expectException('Framework\Base\Application\Exception\MethodNotAllowedException');

        $app->httpRequest('test');
    }

    public function testGuzzleRequestException2()
    {
        $app = $this->getApplication();

        $this::expectException('Framework\Base\Application\Exception\GuzzleHttpException');

        $app->httpRequest('post', 'http://www.google.com');
    }

    public function testGuzzleRequestException3()
    {
        $app = $this->getApplication();

        $this::expectException('Framework\Base\Application\Exception\GuzzleHttpException');

        $app->httpRequest('get', 'http://www.poqwjdoqwidjqowinhdqwiohqwoiqdhwlokiqwndhqwloi.fr');
    }

    public function testGuzzleRequest()
    {
        $app = $this->getApplication();

        $this::assertInstanceOf(
            '\Psr\Http\Message\ResponseInterface',
            $app->httpRequest('get', 'http://www.google.com')
        );
    }
}
