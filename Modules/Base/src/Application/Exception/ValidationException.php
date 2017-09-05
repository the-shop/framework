<?php

namespace Framework\Base\Application\Exception;

/**
 * Class ValidationException
 * @package Framework\Base\Application\Exception
 */
class ValidationException extends \Exception
{
    /**
     * @var array
     */
    private $failedValidations = [];

    /**
     * @param array $validations
     * @return $this
     */
    public function setFailedValidations(array $validations)
    {
        $this->failedValidations = $validations;

        return $this;
    }

    /**
     * @return array
     */
    public function getFailedValidations()
    {
        return $this->failedValidations;
    }
}
