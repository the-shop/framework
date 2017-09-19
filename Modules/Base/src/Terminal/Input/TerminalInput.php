<?php

namespace Framework\Base\Terminal\Input;

/**
 * Class TerminalInput
 * @package Framework\Base\TerminalApp\Input
 */
class TerminalInput implements TerminalInputInterface
{
    /**
     * @var string
     */
    private $commandName = '';

    /**
     * @var array
     */
    private $options = [];

    /**
     * TerminalInput constructor.
     * @param null $arguments
     */
    public function __construct($arguments = null)
    {
        if (null === $arguments) {
            $arguments = $_SERVER['argv'];
        }
        $this->setInputCommand(reset($arguments));
        $this->setInputOptions(array_shift($arguments));
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setInputOptions(array $options = [])
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return array
     */
    public function getInputOptions()
    {
        return $this->options;
    }

    /**
     * @param string $commandName
     * @return $this
     */
    public function setInputCommand(string $commandName)
    {
        $this->commandName = $commandName;

        return $this;
    }

    /**
     * @return string
     */
    public function getInputCommand()
    {
        return $this->commandName;
    }
}
