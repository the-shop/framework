<?php

namespace Framework\Base\Terminal\Input;

/**
 * Interface TerminalInputInterface
 * @package Framework\Base\Terminal\Input
 */
interface TerminalInputInterface
{
    /**
     * @param string $argument
     * @return TerminalInputInterface
     */
    public function setInputCommand(string $argument);

    /**
     * @return mixed
     */
    public function getInputCommand();

    /**
     * @param array $arguments
     * @return TerminalInputInterface
     */
    public function setInputOptions(array $arguments);

    /**
     * @return mixed
     */
    public function getInputOptions();
}
