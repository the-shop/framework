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
     * @return array
     */
    public function getDatabaseAttributes();
}
