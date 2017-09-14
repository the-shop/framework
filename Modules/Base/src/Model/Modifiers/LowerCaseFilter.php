<?php

namespace Framework\Base\Model\Modifiers;

/**
 * Class LowerCaseFilter
 * @package Framework\Base\Model\Modifiers
 */
class LowerCaseFilter implements FieldModifierInterface
{
    /**
     * @param $value
     * @return string
     * @throws \Exception
     */
    public function modify($value)
    {
        if (is_string($value) === false) {
            throw new \Exception("Invalid input. Must be type of string.", 403);
        }

        return strtolower($value);
    }
}
