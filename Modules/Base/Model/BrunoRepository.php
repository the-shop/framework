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

    private $repositoryManager = null;

    public function setDatabaseAdapter(DatabaseAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    public function getDatabaseAdapter()
    {
        return $this->getRepositoryManager()->getDatabaseAdapter();
    }

    /**
     * @param RepositoryManagerInterface $repositoryManager
     * @return $this
     */
    public function setRepositoryManager(RepositoryManagerInterface $repositoryManager)
    {
        $this->repositoryManager = $repositoryManager;

        return $this;
    }

    /**
     * @return RepositoryManagerInterface|null
     */
    public function getRepositoryManager()
    {
        return $this->repositoryManager;
    }

    public function getModelClassName()
    {
        $repositoryClass = get_class($this);

        return $this->getRepositoryManager()->getModelClass($repositoryClass);
    }

    /**
     * @param $identifier
     * @return mixed
     */
    public function loadOne($identifier)
    {
        $modelClass = $this->getModelClassName();

        $model = new $modelClass();

        $query = $this->createNewQueryForModel($model);

        $data = $this->getDatabaseAdapter()
            ->loadOne($query);

        $attributes = $data->getArrayCopy();

        $model->setAttributes($attributes);
        $model->setDatabaseAttributes($attributes);
        $model->setIsNew(false);

        return $model;
    }

    /**
     * @param BrunoInterface $bruno
     * @return BrunoInterface
     */
    public function save(BrunoInterface $bruno)
    {
        // TODO: Implement save() method.
    }

    private function createNewQueryForModel(BrunoInterface $bruno)
    {
        $query = new MongoQuery();
        $query->setDatabase($bruno->getDatabase());
        $query->setCollection($bruno->getCollection());
        return $query;
    }
}
