<?php

namespace Framework\Base\Model;

use MongoDB\Client;
use MongoDB\Exception\InvalidArgumentException;

/**
 * Base Model for database
 *
 * @package Framework\Base\Model
 * @property bool new returns if exists in db
 */
abstract class Bruno
{
    /**
     * @var Client
     */
    private $client;

    protected $address;

    protected $database;

    protected $collection;

    /**
     * @var \MongoDB\Collection
     */
    private $currentCollection;

    /**
     * @var array
     */
    private $dbAttributes = [];

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var bool
     */
    private $isNew = true;

    public function __construct(array $attributes = [])
    {
        $this->client = new Client("mongodb://" . $this->address);
        try {
            $this->currentCollection = $this->client->selectCollection($this->getDatabase(), $this->getCollection());
        } catch (InvalidArgumentException $exception) {
            //TODO throw new Exception
        }
        $this->attributes = $attributes;
    }

    public function getId()
    {
        return isset($this->getDBAttributes()['_id']) ? $this->getDBAttributes()['_id'] : null;
    }

    public function save()
    {
        if ($this->isNew()) {
            $this->getCurrentCollection()->insertOne($this->getAttributes());
            $this->isNew = false;
            $this->dbAttributes = $this->getAttributes();
        } else {
            $this->getCurrentCollection()->updateOne(['_id' => $this->getId()], $this->getAttributes());
        }
        return $this;
    }
    public function isNew()
    {
        return $this->isNew;
    }

    protected function getCollection()
    {
        return isset($this->collection) ? $this->collection : null;
    }

    protected function getDatabase()
    {
        return isset($this->database) ? $this->database : null;
    }

    private function getCurrentCollection()
    {
        return $this->currentCollection;
    }

    private function getAttributes()
    {
        return $this->attributes;
    }

    private function getDBAttributes()
    {
        return $this->dbAttributes;
    }
}
