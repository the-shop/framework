<?php

namespace Framework\Base\Validation\Validations;

/**
 * Class AlphaDashValidation
 * @package Framework\Base\Validation\Validations
 */
class AlphaDashValidation extends Validation
{
    /**
     * Validate that an attribute contains only alpha-numeric characters, dashes, and underscores.
     * @return bool
     */
    public function isValid()
    {
        if (empty($this->getValue()) === true) {
            return true;
        }

        return preg_match('/^[\pL\pM\pN_-]+$/u', $this->getValue()) > 0 === true;
    }

    /**
     * @return string
     */
    public function getRuleName()
    {
        return 'isAlphaDashed';
    }
}
