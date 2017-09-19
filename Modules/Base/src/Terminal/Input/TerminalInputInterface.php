<?php

namespace Framework\Base\Terminal\Input;

/**
 * Interface TerminalInputInterface
 * @package Framework\Base\TerminalApp\Input
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
     * @param array $options
     * @return mixed
     */
    public function setInputOptions(array $options = []);

    /**
     * @return mixed
     */
    public function getInputOptions();
}
