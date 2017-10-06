<?php

namespace Framework\Base\Model\Modifiers;

/**
 * Class HashFilter
 * @package Framework\Base\Model\Modifiers
 */
class HashFilter implements FieldModifierInterface
{
    /**
     * @param $value
     * @return bool|string
     */
    public function modify($value)
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }
}
