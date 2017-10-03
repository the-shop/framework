<?php

namespace Framework\Base\Test;

use Framework\Base\Database\DatabaseAdapterInterface;
use Framework\Base\Database\DatabaseQueryInterface;

class TestDatabaseAdapter implements DatabaseAdapterInterface
{
    /**
     * @var null
     */
    private $loadOneResult = null;

    /**
     * @param \Framework\Base\Database\DatabaseQueryInterface $query
     * @param array                                           $data
     *
     * @return mixed
     */
    public function insertOne(DatabaseQueryInterface $query, array $data = [])
    {
        return 'Not implemented';
    }

    /**
     * @param \Framework\Base\Database\DatabaseQueryInterface $query
     *
     * @return mixed
     */
    public function loadOne(DatabaseQueryInterface $query)
    {
        return $this->loadOneResult;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setLoadOneResult($value)
    {
        $this->loadOneResult = $value;
        return $this;
    }

    /**
     * @param \Framework\Base\Database\DatabaseQueryInterface $query
     *
     * @return mixed
     */
    public function loadMultiple(DatabaseQueryInterface $query)
    {
        return 'Not implemented';
    }

    /**
     * @param \Framework\Base\Database\DatabaseQueryInterface $query
     * @param string                                          $identifier
     * @param array                                           $updateData
     *
     * @return mixed
     */
    public function updateOne(DatabaseQueryInterface $query, string $identifier, array $updateData = [])
    {
        return 'Not implemented';
    }

    /**
     * @param \Framework\Base\Database\DatabaseQueryInterface $query
     *
     * @return mixed
     */
    public function deleteOne(DatabaseQueryInterface $query)
    {
        return 'Not implemented';
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return 'Not implemented';
    }

    /**
     * @param \Framework\Base\Database\DatabaseAdapterInterface $client
     *
     * @return mixed
     */
    public function setDatabaseAdapter(DatabaseAdapterInterface $client)
    {
        return 'Not implemented';
    }

    /**
     * @return mixed
     */
    public function getDatabaseAdapter()
    {
        return 'Not implemented';
    }

    /**
     * @return \Framework\Base\Database\DatabaseQueryInterface
     */
    public function newQuery(): DatabaseQueryInterface
    {
        return new TestDatabaseQuery();
    }
}
