<?php

namespace Framework\Base\Test\Terminal\Output;

use Framework\Base\Terminal\Output\ColorFormatter;
use Framework\Base\Terminal\Output\TerminalOutput;
use Framework\Base\Test\UnitTest;
use Framework\Http\Response\Response;
use InvalidArgumentException;

/**
 * Class TerminalOutputTest
 * @package Framework\Base\Test\Terminal\Output
 */
class TerminalOutputTest extends UnitTest
{
    /**
     * Test terminalOutput constructor
     */
    public function testTerminalOutputConstructor()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The TerminalOutput class needs a stream as its first argument.'
        );
        $this->expectExceptionCode(404);

        new TerminalOutput('test');
    }

    /**
     * Test output message on status code 200
     */
    public function testTerminalOutputMessage()
    {
        $stream = fopen(__DIR__ . '/outputMessages.txt', 'w');
        $outputHandler = new TerminalOutput($stream);

        $response = new Response();
        $response->setCode(200);
        $response->setBody('test');

        $outputHandler->render($response);

        $colorFormatter = new ColorFormatter();

        $foregroundColors = $colorFormatter->getForegroundColors();
        $backgroundColors = $colorFormatter->getBackgroundColors();

        $this->assertEquals(
            [
                "\033["
                . $foregroundColors['green']
                . "m"
                . "\033["
                . $backgroundColors['black']
                . "m"
                . "Status code: 200 command DONE!"
                . "\033[0m",
                "\033["
                . $foregroundColors['green']
                . "m"
                . "\033[" . $backgroundColors['black']
                . "m"
                . "Response: "
                . "\033[0m"
                . '"test"'
            ],
            $outputHandler->getOutputMessages()
        );

        $this->clearOutputFile();
    }

    /**
     * Test output message on exception
     */
    public function testTerminalOutputMessageException()
    {
        $stream = fopen(__DIR__ . '/outputMessages.txt', 'w');
        $outputHandler = new TerminalOutput($stream);

        $response = new Response();
        $response->setCode(403);
        $response->setBody([
            'error' => true,
            'errors' => 'Test Exception message.'
        ]);

        $outputHandler->render($response);

        $colorFormatter = new ColorFormatter();

        $foregroundColors = $colorFormatter->getForegroundColors();
        $backgroundColors = $colorFormatter->getBackgroundColors();

        $this->assertEquals(
            [
                "\033["
                . $foregroundColors['red']
                . "m"
                . "\033["
                . $backgroundColors['light_gray']
                . "m"
                . "Status code: 403 command FAILED!"
                . "\033[0m",
                "\033["
                . $foregroundColors['red']
                . "m"
                . "\033[" . $backgroundColors['light_gray']
                . "m"
                . "Response: "
                . json_encode($response->getBody())
                . "\033[0m"
            ],
            $outputHandler->getOutputMessages()
        );

        $this->clearOutputFile();
    }

    /**
     * Clear output messages from file
     */
    private function clearOutputFile()
    {
        file_put_contents(__DIR__ . '/outputMessages.txt', '');
    }
}
