<?php

namespace Framework\Base\Terminal\Output;

/**
 * Interface TerminalOutputInterface
 * @package Framework\Base\Terminal\Output
 */
interface TerminalOutputInterface
{
    /**
     * @param array $messages
     * @return mixed
     */
    public function setOutputMessages(array $messages);

    /**
     * @return mixed
     */
    public function getOutputMessages();

    /**
     * @param string $message
     * @param bool $newline
     * @return mixed
     */
    public function writeOutput(string $message, bool $newline);
}
