<?php

namespace Framework\Base\Test\Application;

use Framework\Base\Application\Configuration;
use Framework\Base\Test\UnitTest;

/**
 * Class ConfigurationTest
 * @package Framework\Base\Test\Application
 */
class ConfigurationTest extends UnitTest
{
    public function testSetGet()
    {
        $config = new Configuration();
        $config->setPathValue('test', 'value');

        $this->assertEquals(['test' => 'value'], $config->getAll());

        $config->setPathValue('test.nested.value', ['array', 'value']);

        // Overwrite
        $this->assertEquals(['test' => ['nested' => ['value' => ['array', 'value']]]], $config->getAll());

        // Write in parallel
        $config->setPathValue('parallel.nested.value', ['array2', 'value2']);

        $this->assertEquals([
            'test' =>
                [
                    'nested' => [
                        'value' => [
                            'array',
                            'value'
                        ]
                    ]
                ],
            'parallel' =>
                [
                    'nested' => [
                        'value' => [
                            'array2',
                            'value2'
                        ]
                    ]
                ],
        ], $config->getAll());

        // Test get at path
        $this->assertEquals('value2', $config->getPathValue('parallel.nested.value.1'));
    }
}
