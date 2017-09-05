<?php

namespace Framework\Base\Validation\Validations;

/**
 * Class IntegerValidation
 * @package Framework\Base\Validation\Validations
 */
class IntegerValidation extends Validation
{
    /**
     * @return bool
     */
    public function isValid()
    {
        return is_int($this->getValue()) === true;
    }

    /**
     * @return string
     */
    public function getRuleName()
    {
        return 'isInteger';
    }
}
