<?php

namespace Framework\Application\RestApi;

use Throwable;

/**
 * Class MethodNotAllowedException
 * @package Framework\Application\RestApi
 */
class MethodNotAllowedException extends \Exception
{
    /**
     * @var array
     */
    private $allowedMethods = [];

    /**
     * MethodNotAllowedException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param array $methods
     * @return $this
     */
    public function setAllowedMethods(array $methods = [])
    {
        $this->allowedMethods = $methods;

        return $this;
    }
}
