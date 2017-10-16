<?php

namespace Framework\Terminal\Input;

use Framework\Base\Application\ApplicationAwareInterface;

/**
 * Interface TerminalInputInterface
 * @package Framework\Base\TerminalApp\Input
 */
interface TerminalInputInterface extends ApplicationAwareInterface
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
     * @return mixed
     */
    public function setInputParameters(array $arguments = []);

    /**
     * @return mixed
     */
    public function getInputParameters();
}
