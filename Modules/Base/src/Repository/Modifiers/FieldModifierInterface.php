<?php

namespace Framework\Base\Repository\Modifiers;

/**
 * Interface FieldModifierInterface
 * @package Framework\Base\RepositoryManager\Modifiers
 */
interface FieldModifierInterface
{
    /**
     * @param $value
     * @return mixed
     */
    public function modify($value);
}
