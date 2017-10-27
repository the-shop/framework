<?php

namespace Framework\Base\Model;

use Framework\Base\Application\ApplicationAwareInterface;
use Framework\Base\Model\Modifiers\FieldModifierInterface;

/**
 * Interface BrunoInterface
 * @package Framework\Base\Model
 */
interface BrunoInterface extends ApplicationAwareInterface
{
    /**
     * @return string|null
     */
    public function getId();

    /**
     * @return string|null
     */
    public function getPrimaryKey();

    /**
     * @param string $primaryKey
     * @return BrunoInterface
     */
    public function setPrimaryKey(string $primaryKey);

    /**
     * @return bool
     */
    public function isNew();

    /**
     * @param bool $flag
     * @return BrunoInterface
     */
    public function setIsNew(bool $flag = true);

    /**
     * @return BrunoInterface
     */
    public function save();

    /**
     * @return BrunoInterface
     */
    public function delete();

    /**
     * @return array
     */
    public function getAttributes();

    /**
     * @param array $attributes
     * @return BrunoInterface
     */
    public function setAttributes(array $attributes = []);

    /**
     * @param string $attribute
     * @param mixed $value
     * @return mixed
     */
    public function setAttribute(string $attribute, $value);

    /**
     * @return array
     */
    public function getDirtyAttributes();

    /**
     * @return boolean
     */
    public function isDirty();

    /**
     * @param array $attributes
     * @return BrunoInterface
     */
    public function setDatabaseAttributes(array $attributes = []);

    /**
     * @return array
     */
    public function getDatabaseAttributes();

    /**
     * @param string $attributeName
     * @return mixed|null
     */
    public function getDatabaseAttribute(string $attributeName);

    /**
     * @param string $databaseAddress
     *
     * @return \Framework\Base\Model\BrunoInterface
     */
    public function setDatabaseAddress(string $databaseAddress): BrunoInterface;

    /**
     * @return string
     */
    public function getDatabaseAddress();

    /**
     * @param string $databaseName
     *
     * @return \Framework\Base\Model\BrunoInterface
     */
    public function setDatabase(string $databaseName): BrunoInterface;

    /**
     * @return string
     */
    public function getDatabase();

    /**
     * @param string $collectionName
     * @return BrunoInterface
     */
    public function setCollection(string $collectionName);

    /**
     * @return string
     */
    public function getCollection();

    /**
     * Set allowed attributes of model
     *
     * @param array $definition
     *
     * @return \Framework\Base\Model\BrunoInterface
     */
    public function defineModelAttributes(array $definition = []): BrunoInterface;

    /**
     * @param string                 $field
     * @param FieldModifierInterface $filter
     *
     * @return \Framework\Base\Model\BrunoInterface
     */
    public function addFieldFilter(string $field, FieldModifierInterface $filter): BrunoInterface;

    /**
     * @return array
     */
    public function getFieldFilters(): array;

    /**
     * @param string $attributeName
     *
     * @return mixed|null
     */
    public function getAttribute(string $attributeName);

    /**
     * @return BrunoInterface
     */
    public function enableFieldFilters();

    /**
     * @return BrunoInterface
     */
    public function disableFieldFilters();

    /**
     * @param string $resourceType
     * @return BrunoInterface
     */
    public function confirmResourceOf(string $resourceType);
}
