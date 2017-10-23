<?php

namespace Framework\Base\Test;

use Framework\Base\Database\DatabaseQueryInterface;

class TestDatabaseQuery implements DatabaseQueryInterface
{
    private $database = '';
    private $collection = '';
//    private $offset = '';
//    private $limit = '';
//    private $selectFields = [];
//    private $conditions = [];
    /**
     * @param string $name
     *
     * @return mixed
     */
    public function setDatabase(string $name)
    {
        $this->database = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function setCollection(string $name)
    {
        $this->collection = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @return mixed
     */
    public function build()
    {
        return $this;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function addSelectField(string $name)
    {
        return 'Not implemented';
    }

    /**
     * @return mixed
     */
    public function getSelectFields()
    {
        return 'Not implemented';
    }

    /**
     * @param int $limit
     *
     * @return mixed
     */
    public function setLimit(int $limit)
    {
        return 'Not implemented';
    }

    /**
     * @return mixed
     */
    public function getLimit()
    {
        return 'Not implemented';
    }

    /**
     * @param int $offset
     *
     * @return mixed
     */
    public function setOffset(int $offset)
    {
        return 'Not implemented';
    }

    /**
     * @return mixed
     */
    public function getOffset()
    {
        return 'Not implemented';
    }

    /**
     * @param string $field
     * @param string $condition
     * @param        $value
     *
     * @return mixed
     */
    public function addAndCondition(string $field, string $condition, $value)
    {
        return 'Not implemented';
    }

    public function whereInArrayCondition(string $field, $value = [])
    {
        return 'Not implemented';
    }

    /**
     * @param string $identifier
     * @return void
     */
    public function setOrderBy(string $identifier)
    {
    }

    /**
     * @return string
     */
    public function getOrderBy()
    {
        return 'Not implemented';
    }

    /**
     * @param string $orderDirection
     * @return void
     */
    public function setOrderDirection(string $orderDirection)
    {
    }

    /**
     * @return string
     */
    public function getOrderDirection()
    {
        return 'Not implemented';
    }
}
