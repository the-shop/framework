<?php

namespace Framework\Base\Validation\Validations;

/**
 * Interface ValidationInterface
 * @package Framework\Base\Validation\Validations
 */
interface ValidationInterface
{
    /**
     * @return bool
     */
    public function isValid();

    /**
     * ValidationInterface constructor. Sets value to class
     * @param $value
     */
    public function __construct($value);

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @return string
     */
    public function getRuleName();
}
