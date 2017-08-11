<?php

namespace Framework\Base\Model;

use Framework\Base\Database\DatabaseAdapterInterface;
use Framework\Base\Database\MongoAdapter;
use Framework\Base\Database\MongoQuery;

/**
 * Base Model for database
 *
 * @package Framework\Base\Model
 * @property bool new returns if exists in db
 */
abstract class Bruno implements BrunoInterface
{
    /**
     * @var MongoAdapter
     */
    protected $databaseAdapter;

    protected $databaseAddress = '192.168.33.10:27017'; // TODO: use this

    protected $database = 'framework';

    protected $collection = 'users';

    /**
     * @var array
     */
    private $dbAttributes = [];

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var bool
     */
    private $isNew = true;

    public function __construct(array $attributes = [])
    {
        $mongoAdapter = new MongoAdapter();

        $this->setDatabaseAdapter($mongoAdapter);

        $this->attributes = $attributes;
    }

    public function getId()
    {
        return isset($this->getDatabaseAttributes()['_id']) ? $this->getDatabaseAttributes()['_id'] : null;
    }

    public function save()
    {
        $query = new MongoQuery();
        $query->setDatabase($this->getDatabase());
        $query->setCollection($this->getCollection());

        if ($this->isNew()) {
            $id = $this->getDatabaseAdapter()->insertOne($query, $this->getAttributes());
            $this->attributes['_id'] = (string) $id;
            $this->isNew = false;
            $this->dbAttributes = $this->getAttributes();
        } else {
            $this->getDatabaseAdapter()->updateOne($query, $this->getId(), $this->getAttributes());
            $this->dbAttributes = $this->getAttributes();
        }

        return $this;
    }

    public function getDatabase()
    {
        return $this->database;
    }

    public function getCollection()
    {
        return $this->collection;
    }

    public function isNew()
    {
        return $this->isNew;
    }

    public function setDatabaseAdapter(DatabaseAdapterInterface $adapter)
    {
        $this->databaseAdapter = $adapter;

        return $this;
    }

    public function getDatabaseAdapter()
    {
        return $this->databaseAdapter;
    }

    public function setAttributes(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getDirtyAttributes()
    {
        // TODO: Implement getDirtyAttributes() method.
    }

    public function getDatabaseAttributes()
    {
        return $this->dbAttributes;
    }
}