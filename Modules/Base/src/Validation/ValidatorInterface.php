<?php

namespace Framework\Base\Validation;

/**
 * Interface ValidatorInterface
 * @package Framework\Base\Validation
 */
interface ValidatorInterface
{
    /**
     * @param $value
     * @param string $rule
     * @return mixed
     */
    public function addValidation($value, string $rule);

    /**
     * @return $this
     * @throws \Framework\Base\Application\Exception\ValidationException
     */
    public function validate();

    /**
     * @return \Framework\Base\Validation\Validations\ValidationInterface[]
     */
    public function getValidations();

    /**
     * @return array
     */
    public function getFailed();
}
