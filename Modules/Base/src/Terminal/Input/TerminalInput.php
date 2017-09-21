<?php

namespace Framework\Base\Terminal\Input;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Request\RequestInterface;

/**
 * Class TerminalInput
 * @package Framework\Base\TerminalApp\Input
 */
class TerminalInput implements TerminalInputInterface
{
    use ApplicationAwareTrait;
    /**
     * @var string
     */
    private $commandName = '';

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * TerminalInput constructor.
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $arguments = $request->getServer()['argv'];
        // Remove script input name
        array_shift($arguments);

        if (empty($arguments) === true) {
            throw new \InvalidArgumentException('No arguments passed.', 403);
        }

        $this->setInputCommand(array_shift($arguments));
        $this->setInputParameters($arguments);
    }

    /**
     * @param array $arguments
     * @return $this
     */
    public function setInputParameters(array $arguments = [])
    {
        $requiredParams = [];
        $optionalParams = [];

        foreach ($arguments as $argument) {
            // Set required parameter
            if (stripos($argument, '=') !== false
                && stripos($argument, '[') === false
            ) {
                $formattedParam = $this->formatInputArgument($argument);
                $requiredParams = array_merge($requiredParams, $formattedParam);
            }
            // Set optional parameter
            if (substr($argument, 0, strlen('[')) === '['
                && substr($argument, -1) === ']'
                && stripos($argument, '=') !== false
            ) {
                $formattedParam = $this->formatInputArgument($argument, true);
                $optionalParams = array_merge($optionalParams, $formattedParam);
            }
        }

        $this->parameters['requiredParams'] = $requiredParams;
        $this->parameters['optionalParams'] = $optionalParams;

        return $this;
    }

    /**
     * @return array
     */
    public function getInputParameters()
    {
        return $this->parameters;
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

    /**
     * @param $argument
     * @param bool $optional
     * @return array
     */
    private function formatInputArgument($argument, $optional = false)
    {
        if ($optional !== false) {
            $removedFirstBracket = str_replace('[', '', $argument);
            $removedBrackets = str_replace(']', '', $removedFirstBracket);
            $argParts = explode('=', $removedBrackets);
        } else {
            $argParts = explode('=', $argument);
        }

        $formattedParameter = [strtolower($argParts[0]) => $argParts[1]];

        $exceptionMessage = '';
        if (empty($argParts[0]) === true && empty($argParts[1])) {
            $exceptionMessage = 'Invalid input! Argument should be passed as <key=value>';
        } elseif (empty($argParts[0]) === true) {
            $exceptionMessage = 'Invalid argument! Value <' . $argParts[1] . '> is passed without key!';
        } elseif (empty($argParts[1]) === true) {
            $exceptionMessage = 'Invalid argument! Key <' . $argParts[0] . '> is passed without value!';
        }

        if (empty($exceptionMessage) !== true) {
            throw new \InvalidArgumentException($exceptionMessage, 403);
        }

        return $formattedParameter;
    }
}