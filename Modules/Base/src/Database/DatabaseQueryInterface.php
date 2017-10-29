<?php

namespace Framework\Base\Database;

/**
 * Interface DatabaseQueryInterface
 * @package Framework\Base\Database
 */
interface DatabaseQueryInterface
{
    /**
     * @param string $name
     * @return \Framework\Base\Database\DatabaseQueryInterface
     */
    public function setDatabase(string $name);

    /**
     * @return string
     */
    public function getDatabase();

    /**
     * @param string $name
     * @return \Framework\Base\Database\DatabaseQueryInterface
     */
    public function setCollection(string $name);

    /**
     * @return string
     */
    public function getCollection();

    /**
     * @return array
     */
    public function build();

    /**
     * @param string $name
     * @return \Framework\Base\Database\DatabaseQueryInterface
     */
    public function addSelectField(string $name);

    /**
     * @return array
     */
    public function getSelectFields();

    /**
     * @param int $limit
     * @return \Framework\Base\Database\DatabaseQueryInterface
     */
    public function setLimit(int $limit);

    /**
     * @return int
     */
    public function getLimit();

    /**
     * @param int $offset
     * @return \Framework\Base\Database\DatabaseQueryInterface
     */
    public function setOffset(int $offset);

    /**
     * @return int
     */
    public function getOffset();

    /**
     * @param string $field
     * @param string $condition
     * @param        $value
     *
     * @return \Framework\Base\Database\DatabaseQueryInterface
     */
    public function addAndCondition(string $field, string $condition, $value);

    /**
     * @param string $field
     * @param $value
     *
     * @return \Framework\Base\Database\DatabaseQueryInterface
     */
    public function whereInArrayCondition(string $field, $value = []);

    /**
     * @param string $identifier
     *
     * @return \Framework\Base\Database\DatabaseQueryInterface
     */
    public function setOrderBy(string $identifier);

    /**
     * @return string
     */
    public function getOrderBy();

    /**
     * @param string $orderDirection
     *
     * @return \Framework\Base\Database\DatabaseQueryInterface
     */
    public function setOrderDirection(string $orderDirection);

    /**
     * @return string
     */
    public function getOrderDirection();

    // TODO: implement
    // public function addOrCondition();
}
