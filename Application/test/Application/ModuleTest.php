<?php

namespace Application\Test\Application;

use Framework\Base\Test\UnitTest;
use Framework\RestApi\Module;

/**
 * Class ModuleTest
 * @package ApplicationTest\Application
 */
class ModuleTest extends UnitTest
{
    public function testModuleIsInstantiable()
    {
        $module = new Module();

        $this->assertInstanceOf(Module::class, $module);
    }
}
