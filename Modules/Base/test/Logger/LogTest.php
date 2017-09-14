<?php

namespace Framework\Base\Test\Logger;

use Framework\Base\Logger\Log;
use Framework\Base\Test\UnitTest;

class LogTest extends UnitTest
{
    public function testIsInstantiable()
    {
        $payload = 'test';
        $log = new Log($payload);

        $this->assertInstanceOf(Log::class, $log);

        $this->assertEquals($payload, $log->getPayload());
    }

    public function testSetData()
    {
        $payload = 'test';
        $log = new Log($payload);

        $log->setData('testKey', 'testValue');

        $this->assertAttributeContains('testValue', 'data', $log);

        $this->assertArrayHasKey('testKey', $log->getAllData());

        $this->assertEquals('testValue', $log->getData('testKey'));
    }

    public function testIsException()
    {
        $payload = 'test';
        $log = new Log($payload);

        $this->assertFalse($log->isException());

        $payload = new \Exception($payload);
        $log = new Log($payload);

        $this->assertTrue($log->isException());
    }
}
