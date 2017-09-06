<?php

namespace Framework\Base\Repository\Modifiers;

/**
 * Interface FieldModifierInterface
 * @package Framework\Base\Repository\Modifiers
 */
interface FieldModifierInterface
{
    /**
     * @param $value
     * @return mixed
     */
    public function modify($value);
}
