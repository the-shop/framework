<?php

namespace Framework\Terminal\Test\Output;

use Framework\Terminal\Output\ColorFormatter;
use Framework\Base\Test\UnitTest;

/**
 * Class ColorFormatterTest
 * @package Framework\Base\Test\Terminal\Output
 */
class ColorFormatterTest extends UnitTest
{
    /**
     * Test color formatter setup colors
     */
    public function testColorFormatterSetupColors()
    {
        $formatter = new ColorFormatter();

        $this->assertNotEmpty($formatter->getBackgroundColors());
        $this->assertNotEmpty($formatter->getForegroundColors());
    }

    /**
     * Test color formatter to color string only foreground
     */
    public function testColorFormatterColoredStringOnlyForeground()
    {
        $formatter = new ColorFormatter();

        $coloredString = $formatter->getColoredString('test', 'red');

        $this->assertEquals(
            "\033[0;31m"
            . 'test'
            . "\033[0m",
            $coloredString
        );
    }

    /**
     * Test color formatter to color string only foreground
     */
    public function testColorFormatterColoredStringOnlyBackground()
    {
        $formatter = new ColorFormatter();

        $coloredString = $formatter->getColoredString(
            'test',
            null,
            'cyan'
        );

        $this->assertEquals(
            "\033[46m"
            . 'test'
            . "\033[0m",
            $coloredString
        );
    }

    /**
     * Test color formatter to color string foreground and background
     */
    public function testColorFormatterColoredStringForegroundAndBackground()
    {
        $formatter = new ColorFormatter();

        $coloredString = $formatter->getColoredString(
            'test',
            'red',
            'cyan'
        );

        $this->assertEquals(
            "\033[0;31m"
            .
            "\033[46m"
            . 'test'
            . "\033[0m",
            $coloredString
        );
    }
}
