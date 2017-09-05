<?php

namespace Framework\Base\Validation\Validations;

/**
 * Class Validation
 * @package Framework\Base\Validation\Validations
 */
abstract class Validation implements ValidationInterface
{
    /**
     * Value to be validated
     * @var
     */
    private $value;

    /**
     * Validation constructor.
     * @param $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
