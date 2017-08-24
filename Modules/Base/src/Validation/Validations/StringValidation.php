<?php

namespace Framework\Base\Validation\Validations;

/**
 * Class StringValidation
 * @package Framework\Base\Validation\Validations
 */
class StringValidation extends Validation
{
    /**
     * @return bool
     */
    public function isValid()
    {
        return is_string($this->getValue()) === true;
    }

    /**
     * @return string
     */
    public function getRuleName()
    {
        return 'isString';
    }
}
