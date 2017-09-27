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
    const REQUIRED_ARG = 'required_argument';
    const OPTIONAL_ARG = 'optional_argument';

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
     * Input arguments that have "=" (equality) symbol are considered as input arguments.
     * If input argument is surrounded with brackets "[]", it will be considered as optional
     * parameter.
     * All other parameters, without equality symbol will not be validated and will be ignored.
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
                $formattedParam = $this->formatInputArgument($argument, self::REQUIRED_ARG);
                $requiredParams = array_merge($requiredParams, $formattedParam);
            }
            // Set optional parameter
            if (substr($argument, 0, strlen('[')) === '['
                && substr($argument, -1) === ']'
                && stripos($argument, '=') !== false
            ) {
                $formattedParam = $this->formatInputArgument($argument, self::OPTIONAL_ARG);
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
     * @param $argumentType
     * @return array
     */
    private function formatInputArgument($argument, $argumentType)
    {
        $argParts = [];

        // Parse optional argument, remove brackets "[", "]", remove "=" and set key <-> value pair
        if ($argumentType === self::OPTIONAL_ARG) {
            $removedFirstBracket = str_replace('[', '', $argument);
            $removedBrackets = str_replace(']', '', $removedFirstBracket);
            $argParts = explode('=', $removedBrackets);
        }

        // Parse required argument, remove "=" and set key <-> value pair
        if ($argumentType === self::REQUIRED_ARG) {
            $argParts = explode('=', $argument);
        }

        // Format argument key <-> value pair to lowercase
        $formattedParameter = [strtolower($argParts[0]) => $argParts[1]];

        // Check if some parts of argument are missing
        $exceptionMessage = '';
        if (empty($argParts[0]) === true && empty($argParts[1])) {
            $exceptionMessage = 'Invalid input! Argument should be passed as <key=value>';
        } elseif (empty($argParts[0]) === true) {
            $exceptionMessage = 'Invalid argument! Key is missing for value <' . $argParts[1] . '>';
        } elseif (empty($argParts[1]) === true) {
            $exceptionMessage = 'Invalid argument! Value is missing for key <' . $argParts[0] . '>';
        }

        if (empty($exceptionMessage) !== true) {
            throw new \InvalidArgumentException($exceptionMessage, 403);
        }

        return $formattedParameter;
    }
}
