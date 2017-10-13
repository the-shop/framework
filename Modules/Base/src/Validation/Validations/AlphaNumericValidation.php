<?php

namespace Framework\Base\Validation\Validations;

/**
 * Class AlphaNumericValidation
 * @package Framework\Base\Validation\Validations
 */
class AlphaNumericValidation extends Validation
{
    /**
     * @return bool
     */
    public function isValid()
    {
        return ctype_alnum($this->getValue()) === true;
    }

    /**
     * @return string
     */
    public function getRuleName()
    {
        return 'isAlphaNumeric';
    }
}
