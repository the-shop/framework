<?php

namespace Framework\Base\Model;

use Framework\Application\RestApi\ApplicationAwareInterface;
use Framework\Application\RestApi\ApplicationAwareTrait;
use Framework\Base\Database\DatabaseAdapterInterface;
use Framework\Base\Database\MongoQuery;

class BrunoRepository implements BrunoRepositoryInterface, ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    private $database = 'bruno';
    private $collection = 'bruno';

    private $adapter = null;

    public function setDatabaseAdapter(DatabaseAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    public function getDatabaseAdapter()
    {
        return $this->adapter;
    }

    public function getRepositoryManager()
    {
        $this->getApplication()->getRepositoryManager();
    }

    public function getModelClassName()
    {
        
    }

    /**
     * @param $identifier
     * @return mixed
     */
    public function loadOne($identifier)
    {
        $model = null;

        $query = $this->createNewQuery();

        $data = $this->getDatabaseAdapter()
            ->loadOne($query);

        return $data;
    }

    /**
     * @param BrunoInterface $bruno
     * @return BrunoInterface
     */
    public function save(BrunoInterface $bruno)
    {
        // TODO: Implement save() method.
    }

    private function createNewQuery()
    {
        $query = new MongoQuery();
        $query->setDatabase($this->database);
        $query->setCollection($this->collection);
        return $query;
    }
}
