<?php

namespace Framework\Base\Validation\Validations;

/**
 * Class FloatValidation
 * @package Framework\Base\Validation\Validations
 */
class FloatValidation extends Validation
{
    /**
     * @return bool
     */
    public function isValid()
    {
        return is_float($this->getValue()) === true;
    }

    /**
     * @return string
     */
    public function getRuleName()
    {
        return 'isFloat';
    }
}
