<?php

namespace Framework\Base\Validation\Validations;

/**
 * Class ArrayValidation
 * @package Framework\Base\Validation\Validations
 */
class ArrayValidation extends Validation
{
    /**
     * @return bool
     */
    public function isValid()
    {
        return is_array($this->getValue()) === true;
    }

    /**
     * @return string
     */
    public function getRuleName()
    {
        return 'isArray';
    }
}
