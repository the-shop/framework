<?php

namespace Test\ApplicationTest;

use Framework\RestApi\Module;
use Test\BaseTest;

/**
 * Class ModuleTest
 * @package Test\ApplicationTest
 */
class ModuleTest extends BaseTest
{
    public function testModuleIsInstantiable()
    {
        $module = new Module();

        $this->assertInstanceOf(Module::class, $module);
    }
}
