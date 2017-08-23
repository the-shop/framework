<?php

namespace ApplicationTest\Application;

use Framework\BaseTest\UnitTest;
use Framework\RestApi\Module;

/**
 * Class ModuleTest
 * @package Test\ApplicationTest
 */
class ModuleTest extends UnitTest
{
    public function testModuleIsInstantiable()
    {
        $module = new Module();

        $this->assertInstanceOf(Module::class, $module);
    }
}
