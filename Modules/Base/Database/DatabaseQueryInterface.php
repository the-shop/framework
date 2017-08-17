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
     * @return mixed
     */
    public function setDatabase(string $name);

    /**
     * @return string
     */
    public function getDatabase();

    /**
     * @param string $name
     * @return mixed
     */
    public function setCollection(string $name);

    /**
     * @return string
     */
    public function getCollection();

    /**
     * @return mixed
     */
    public function build();

    /**
     * @param string $name
     * @return mixed
     */
    public function addSelectField(string $name);

    /**
     * @return array
     */
    public function getSelectFields();

    /**
     * @param int $limit
     * @return mixed
     */
    public function setLimit(int $limit);

    /**
     * @return int
     */
    public function getLimit();

    /**
     * @param int $offset
     * @return mixed
     */
    public function setOffset(int $offset);

    /**
     * @return int
     */
    public function getOffset();


    public function addAndCondition(string $field, string $condition, $value);

    // TODO: implement
    // public function addOrCondition();
}
