<?php

namespace Framework\RestApiTest\Listener;

use Framework\BaseTest\UnitTest;
use Framework\RestApi\Listener\ExceptionFormatter;

class ExceptionFormatterTest extends UnitTest
{
    public function testEquals()
    {
        $application = $this->getApplication();

        $formatter = new ExceptionFormatter();
        $formatter->setApplication($application);

        $exception = new \RuntimeException('Unit test runtime exception');

        $formatter->handle($exception);

        $response = $formatter->getApplication()->getResponse();

        $this->assertEquals(500, $response->getCode());
        $this->assertEquals([
            'error' => true,
            'errors' => [$exception->getMessage()]
        ], $response->getBody());
    }
}
