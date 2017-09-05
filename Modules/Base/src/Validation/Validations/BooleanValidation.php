<?php

namespace Framework\Base\Validation\Validations;

/**
 * Class BooleanValidation
 * @package Framework\Base\Validation\Validations
 */
class BooleanValidation extends Validation
{
    /**
     * @return bool
     */
    public function isValid()
    {
        return is_bool($this->getValue()) === true;
    }

    /**
     * @return string
     */
    public function getRuleName()
    {
        return 'isBool';
    }
}
