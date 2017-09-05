<?php

namespace Framework\Base\Validation\Validations;

/**
 * Class NonEmptyValidation
 * @package Framework\Base\Validation\Validations
 */
class NonEmptyValidation extends Validation
{
    /**
     * @return bool
     */
    public function isValid()
    {
        return empty($this->getValue()) === false ||
            is_numeric($this->getValue()) === true;
    }

    /**
     * @return string
     */
    public function getRuleName()
    {
        return 'isNonEmpty';
    }
}
