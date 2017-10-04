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
        $this->assertEquals(
            ['test' => ['nested' => ['value' => ['array', 'value']]]],
            $config->getAll()
        );

        // Write in parallel
        $config->setPathValue('parallel.nested.value', ['array2', 'value2']);

        $this->assertEquals([
            'test' =>
                [
                    'nested' => [
                        'value' => [
                            'array',
                            'value',
                        ],
                    ],
                ],
            'parallel' =>
                [
                    'nested' => [
                        'value' => [
                            'array2',
                            'value2',
                        ],
                    ],
                ],
        ], $config->getAll());

        // Test get at path
        $this->assertEquals('value2', $config->getPathValue('parallel.nested.value.1'));
    }

    /**
     * Test configuration methods readFromPhp and readFromJson
     */
    public function testReadFromPhpAndReadFromJson()
    {
        $config = new Configuration();

        $config->readFromPhp(__DIR__ . '/dummyConfig.php');

        $this->assertEquals([
            'test' => [
                'value1',
                'value2',
            ],
        ], $config->getPathValue('php'));

        $config->readFromJson(__DIR__ . '/dummyConfigJson.json');

        $this->assertEquals('value1', $config->getPathValue('json.test.0'));
        $this->assertEquals([
            'php' => [
                'test' => [
                    'value1',
                    'value2',
                ],
            ],
            'json' => [
                'test' => [
                    'value1',
                    'value2',
                ],
            ],
        ], $config->getAll());
    }

    /**
     * Test configuration readFromPhp method - file not found - exception thrown
     */
    public function testReadFromPhpExceptionThrown()
    {
        $config = new Configuration();

        $file = 'testing.php';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('Unable to open php file' . $file . ' file not found!');

        $config->readFromPhp($file);
    }

    /**
     * Test configuration readFromJson method - file not found - exception thrown
     */
    public function testReadFromJsonExceptionThrown()
    {
        $config = new Configuration();

        $file = 'testing.json';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('Unable to open json file' . $file . ' file not found!');

        $config->readFromJson($file);
    }
}
