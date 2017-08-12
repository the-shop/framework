<?php

namespace Framework\Base\Model;

use Framework\Base\Database\DatabaseAdapterInterface;
use Framework\Base\Database\MongoAdapter;
use Framework\Base\Database\MongoQuery;

/**
 * Base Model for database
 *
 * @package Framework\Base\Model
 */
abstract class Bruno implements BrunoInterface
{
    /**
     * @var DatabaseAdapterInterface
     */
    protected $databaseAdapter;

    /**
     * @var string
     */
    protected $primaryKey = '_id';

    protected $databaseAddress = '192.168.33.10:27017'; // TODO: use this

    /**
     * @var string
     */
    protected $database = 'framework';

    /**
     * @var string
     */
    protected $collection = 'bruno';

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

    /**
     * Bruno constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        // TODO: depend on interface for adapter
        $mongoAdapter = new MongoAdapter();

        $this->setDatabaseAdapter($mongoAdapter);

        $this->attributes = $attributes;
    }

    /**
     * @return bool|null
     */
    public function getId()
    {
        if (isset($this->getDatabaseAttributes()[$this->primaryKey])) {
            return $this->getDatabaseAttributes()[$this->primaryKey];
        }

        return null;
    }

    /**
     * @return $this
     */
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

    /**
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @return string
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @return bool
     */
    public function isNew()
    {
        return $this->isNew;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setIsNew(bool $flag = true)
    {
        $this->isNew = $flag;

        return $this;
    }

    /**
     * @param DatabaseAdapterInterface $adapter
     * @return $this
     */
    public function setDatabaseAdapter(DatabaseAdapterInterface $adapter)
    {
        $this->databaseAdapter = $adapter;

        return $this;
    }

    /**
     * @return DatabaseAdapterInterface
     */
    public function getDatabaseAdapter()
    {
        return $this->databaseAdapter;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function setAttributes(array $attributes = [])
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getDirtyAttributes()
    {
        // TODO: Implement getDirtyAttributes() method.
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function setDatabaseAttributes(array $attributes = [])
    {
        $this->dbAttributes = $attributes;

        return $this;
    }

    /**
     * @return array
     */
    public function getDatabaseAttributes()
    {
        return $this->dbAttributes;
    }
}
