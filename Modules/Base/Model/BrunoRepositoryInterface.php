<?php

namespace Framework\Base\Model;

use Framework\Base\Database\DatabaseAdapterInterface;

/**
 * Interface BrunoRepositoryInterface
 * @package Framework\Base\Model
 */
interface BrunoRepositoryInterface
{
    /**
     * @param DatabaseAdapterInterface $
     * @return mixed
     */
    public function setDatabaseAdapter(DatabaseAdapterInterface $adapter);

    /**
     * @return DatabaseAdapterInterface|null
     */
    public function getDatabaseAdapter();

    /**
     * @param $identifier
     * @return BrunoInterface|null
     */
    public function loadOne($identifier);

    /**
     * @param BrunoInterface $bruno
     * @return BrunoInterface
     */
    public function save(BrunoInterface $bruno);
}
