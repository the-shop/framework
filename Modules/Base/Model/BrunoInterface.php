<?php

namespace Framework\Base\Model;

use Framework\Base\Database\DatabaseAdapterInterface;

/**
 * Interface BrunoInterface
 * @package Framework\Base\Model
 */
interface BrunoInterface
{
    /**
     * @return string
     */
    public function getId();

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
     * @return mixed
     */
    public function setDatabaseAdapter(DatabaseAdapterInterface $adapter);

    /**
     * @return DatabaseAdapterInterface|null
     */
    public function getDatabaseAdapter();

    /**
     * @return array
     */
    public function getAttributes();

    /**
     * @return BrunoInterface
     */
    public function setAttributes(array $attributes = []);

    /**
     * @return array
     */
    public function getDirtyAttributes();

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
     * @return string
     */
    public function getDatabase();

    /**
     * @return string
     */
    public function getCollection();
}
