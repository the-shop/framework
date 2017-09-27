<?php

namespace Framework\Base\Validation\Validations;

/**
 * Class UniqueValidation
 * @package Framework\Base\Validation\Validations
 */
class UniqueValidation extends Validation
{
    /**
     * @return bool
     */
    public function isValid()
    {
        $value = $this->getValue();

        if (is_array($value) !== true
            || (count($value) === 2) !== true
            || (array_key_exists('resourceName', $value)) !== true
        ) {
            throw new \InvalidArgumentException(
                'Invalid input for unique validation',
                403
            );
        }

        // Get identifier and value from input so we can query DB
        $identifierValue = reset($value);
        $identifier = key($value);

        //TODO: implement this call after loadOneBy() is implemented on @BrunoRepositoryInterface
//        $model = $this->getApplication()
//            ->getRepositoryManager()
//            ->getRepositoryFromResourceName($value['resourceName'])
//            ->loadOneBy([$identifier => $identifierValue]);

        // Return true if no model is found, return false if model is found
        return true;
    }

    /**
     * @return string
     */
    public function getRuleName()
    {
        return 'isString';
    }
}
