<?php

namespace Framework\Base\Mongo;

use Framework\Base\Database\DatabaseQueryInterface;
use MongoDB\BSON\ObjectID;

/**
 * Class MongoQuery
 * @package Framework\Base\Mongo
 */
class MongoQuery implements DatabaseQueryInterface
{
    /**
     * @var string
     */
    private $database = '';

    /**
     * @var string
     */
    private $collection = '';

    /**
     * @var string
     */
    private $offset = '';

    /**
     * @var string
     */
    private $limit = '';

    /**
     * @var array
     */
    private $selectFields = [];

    /**
     * @var array
     */
    private $conditions = [];

    /**
     * @return array
     */
    public function build()
    {
        return $this->conditions;
    }

    /**
     * @param string $field
     * @param string $operation
     * @param        $value
     *
     * @return void
     */
    public function addAndCondition(string $field, string $operation, $value)
    {
        if ($operation === '=') {
            $operation = '$eq';
        }

        if ($field === '_id') {
            $value = new ObjectID($value);
        }

        $queryPart = [$field => [$operation => $value]];

        $this->conditions = array_merge_recursive($this->conditions, $queryPart);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setDatabase(string $name)
    {
        $this->database = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setCollection(string $name)
    {
        $this->collection = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function setOffset(int $offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function setLimit(int $limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function addSelectField(string $name)
    {
        $this->selectFields[] = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function getSelectFields()
    {
        return $this->selectFields;
    }
}
