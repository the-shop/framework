<?php

namespace Framework\Base\Database;

/**
 * Interface DatabaseAdapterInterface
 * @package Framework\Base\Database
 */
interface DatabaseAdapterInterface
{
    /**
     * @param DatabaseQueryInterface $query
     * @param array $data
     * @return mixed
     */
    public function insertOne(DatabaseQueryInterface $query, array $data = []);

    /**
     * @param DatabaseQueryInterface $query
     * @return mixed
     */
    public function loadOne(DatabaseQueryInterface $query);

    /**
     * @param DatabaseQueryInterface $query
     * @return \ArrayObject[]
     */
    public function loadMultiple(DatabaseQueryInterface $query);

    /**
     * @param DatabaseQueryInterface $query
     * @param string $identifier
     * @param array $updateData
     * @return mixed
     */
    public function updateOne(DatabaseQueryInterface $query, string $identifier, array $updateData = []);

    /**
     * @param DatabaseQueryInterface $query
     * @return mixed
     */
    public function deleteOne(DatabaseQueryInterface $query);

    /**
     * @return mixed
     */
    public function getClient();

    /**
     * @return \Framework\Base\Database\DatabaseQueryInterface
     */
    public function newQuery(): DatabaseQueryInterface;
}
