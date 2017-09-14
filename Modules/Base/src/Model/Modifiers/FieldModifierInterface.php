<?php

namespace Framework\Base\Model\Modifiers;

/**
 * Interface FieldModifierInterface
 * @package Framework\Base\Model\Modifiers
 */
interface FieldModifierInterface
{
    /**
     * @param $value
     * @return mixed
     */
    public function modify($value);
}
