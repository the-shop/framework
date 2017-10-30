<?php

namespace Framework\Base\Test\Application;

use Framework\Base\Application\ServicesRegistry;
use Framework\Base\Test\UnitTest;

class ServiceRegistryTest extends UnitTest
{
    public function testRegisterWithServiceInterface()
    {
        $val = new SampleService();
        $key = SampleService::class;

        $registry = new ServicesRegistry();
        $registry->setApplication($this->getApplication());

        $this->assertAttributeCount(0, 'content', $registry);

        $registry->registerService($val);

        $this->assertAttributeCount(1, 'content', $registry);

        $this->assertInstanceOf(SampleService::class, $registry->get($key));

        $registry->delete($key);

        $this->assertAttributeCount(0, 'content', $registry);
    }

    public function testRegisterWithWrongInterface()
    {
        $key = 'sampleService';
        $val = new \stdClass();

        $registry = new ServicesRegistry();
        $registry->setApplication($this->getApplication());

        $this->assertAttributeCount(0, 'content', $registry);

        $this->expectException(\RuntimeException::class);

        $registry->register($key, $val);
    }
}
