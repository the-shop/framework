<?php

namespace Framework\Terminal\Test\Commands;

use Framework\Base\Test\UnitTest;

/**
 * Class Test
 * @package Framework\Base\Terminal\Commands
 */
class DummyCommand extends UnitTest
{
    /**
     * @var null
     */
    private $testApp = null;

    /**
     * @param $app
     */
    public function setApplication($app)
    {
        $this->testApp = $app;
    }

    /**
     * @param $testParam
     * @param $testParam2
     * @param null $optionalParam
     * @param null $optionalParam2
     * @return string
     */
    public function handle($testParam, $testParam2, $optionalParam = null, $optionalParam2 = null)
    {
        return $testParam . ', ' . $testParam2 . ', ' . $optionalParam . ', ' . $optionalParam2;
    }
}
