<?php

namespace Framework\Base\Validation\Validations;

/**
 * Class EmailValidation
 * @package Framework\Base\Validation\Validations
 */
class EmailValidation extends Validation
{
    /**
     * @return bool
     */
    public function isValid()
    {
        return is_string($this->getValue()) === true &&
            strlen($this->getValue()) < 254 &&
            filter_var($this->getValue(), FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @return string
     */
    public function getRuleName()
    {
        return 'isEmail';
    }
}
