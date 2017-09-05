<?php

namespace Framework\BaseTest\Application;

use Framework\Base\Test\UnitTest;

class BaseApplicationTest extends UnitTest
{
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
