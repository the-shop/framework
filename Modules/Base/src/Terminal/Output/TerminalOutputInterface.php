<?php

namespace Framework\Base\Terminal\Output;

use Framework\Base\Application\ApplicationAwareInterface;
use Framework\Base\Render\RenderInterface;
use Framework\Base\Terminal\Input\TerminalInputInterface;

/**
 * Interface TerminalOutputInterface
 * @package Framework\Base\TerminalApp\Output
 */
interface TerminalOutputInterface extends RenderInterface, ApplicationAwareInterface
{
    /**
     * @param array $messages
     * @return TerminalOutputInterface
     */
    public function setOutputMessages(array $messages = []);

    /**
     * @return array
     */
    public function getOutputMessages();

    /**
     * @return TerminalInputInterface
     */
    public function outputMessages();

    /**
     * @param string $message
     * @param bool $newline
     * @return TerminalOutputInterface
     */
    public function writeOutput(string $message, bool $newline = false);
}
