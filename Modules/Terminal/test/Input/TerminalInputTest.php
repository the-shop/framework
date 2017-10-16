<?php

namespace Framework\Terminal\Test\Input;

use Framework\Terminal\Input\TerminalInput;
use Framework\Base\Test\UnitTest;
use Framework\Http\Request\Request;

/**
 * Class TerminalInputTest
 * @package Framework\Base\Test\Terminal\Input
 */
class TerminalInputTest extends UnitTest
{
    /**
     * Test CLI input handler - success
     */
    public function testTerminalInputParameters()
    {
        $serverInfo = $_SERVER;
        $serverInfo['argv'] = [
            'yoda.php',
            'testCommand',
            'testParam=testParam',
            'testPARAM2=testing',
            '[testOptional=testOptional]',
        ];
        $request = new Request();
        $request->setServer($serverInfo);
        $inputHandler = new TerminalInput($request);

        $this->assertEquals(
            [
                'testparam' => 'testParam',
                'testparam2' => 'testing',
            ],
            $inputHandler->getInputParameters()['requiredParams']
        );
        $this->assertEquals(
            [
                'testoptional' => 'testOptional',
            ],
            $inputHandler->getInputParameters()['optionalParams']
        );
    }

    /**
     * Test CLI input handler - throw exception - wrong param missing value
     */
    public function testTerminalInputParametersWrongParamNoValue()
    {
        $wrongParam = 'testParam=';

        $serverInfo = $_SERVER;
        $serverInfo['argv'] = [
            'yoda.php',
            'testCommand',
            $wrongParam,
            'testPARAM2=testing',
            '[testOptional=testOptional]',
        ];
        $request = new Request();
        $request->setServer($serverInfo);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid argument! Value is missing for key <'
            . str_replace('=', '', $wrongParam)
            . '>'
        );
        $this->expectExceptionCode(403);
        new TerminalInput($request);
    }

    /**
     * Test CLI input handler - throw exception - param missing key
     */
    public function testTerminalInputParametersWrongParamNoKey()
    {
        $wrongParam = '=testingParamValue';

        $serverInfo = $_SERVER;
        $serverInfo['argv'] = [
            'yoda.php',
            'testCommand',
            $wrongParam,
            'testPARAM2=testing',
            '[testOptional=testOptional]',
        ];
        $request = new Request();
        $request->setServer($serverInfo);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid argument! Key is missing for value <'
            . str_replace('=', '', $wrongParam)
            . '>'
        );
        $this->expectExceptionCode(403);
        new TerminalInput($request);
    }

    /**
     * Test CLI input handler - throw exception - param missing key
     */
    public function testTerminalInputParametersWrongParamNoKeyAndNoValue()
    {
        $wrongParam = '=';

        $serverInfo = $_SERVER;
        $serverInfo['argv'] = [
            'yoda.php',
            'testCommand',
            $wrongParam,
            'testPARAM2=testing',
            '[testOptional=testOptional]',
        ];
        $request = new Request();
        $request->setServer($serverInfo);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid input! Argument should be passed as <key=value>');
        $this->expectExceptionCode(403);
        new TerminalInput($request);
    }
}
