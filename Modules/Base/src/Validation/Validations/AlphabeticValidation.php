<?php

namespace Framework\Base\Validation\Validations;

/**
 * Class AlphabeticValidation
 * @package Framework\Base\Validation\Validations
 */
class AlphabeticValidation extends Validation
{
    /**
     * @return bool
     */
    public function isValid()
    {
        return ctype_alpha($this->getValue()) === true;
    }

    /**
     * @return string
     */
    public function getRuleName()
    {
        return 'isAlphabetic';
    }
}
