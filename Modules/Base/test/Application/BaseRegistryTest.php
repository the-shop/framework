<?php

namespace Framework\Base\Test\Application;

use Framework\Base\Application\BaseRegistry;
use Framework\Base\Test\UnitTest;

class BaseRegistryTest extends UnitTest
{
    public function testRegisterGetAndDelete()
    {
        $val = 1;
        $key = 'test';

        $registry = new BaseRegistry();

        $this->assertAttributeCount(0, 'content', $registry);

        $registry->register($key, $val);

        $this->assertAttributeCount(1, 'content', $registry);

        $this->assertEquals($val, $registry->get($key));

        $registry->delete($key);

        $this->assertAttributeCount(0, 'content', $registry);
    }

    public function testDoubleRegistrationWithException()
    {
        $val = 1;
        $key = 'test';

        $registry = new BaseRegistry();

        $this->expectException(\RuntimeException::class);

        $registry->register($key, $val);
        $registry->register($key, $val);
    }

    public function testDoubleRegistrationWithOverwrite()
    {
        $val1 = 1;
        $val2 = 1;
        $key = 'test';

        $registry = new BaseRegistry();

        $registry->register($key, $val1);

        $this->assertEquals($val1, $registry->get($key));

        $registry->register($key, $val2, true);

        $this->assertAttributeCount(1, 'content', $registry);
        $this->assertEquals($val2, $registry->get($key));
    }
}
