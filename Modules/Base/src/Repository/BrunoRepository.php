<?php

namespace Framework\Base\Repository;

use Framework\Base\Application\ApplicationAwareInterface;
use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Database\DatabaseAdapterInterface;
use Framework\Base\Database\DatabaseQueryInterface;
use Framework\Base\Manager\RepositoryInterface;
use Framework\Base\Model\BrunoInterface;
use Framework\Base\Mongo\MongoQuery;

/**
 * Class BrunoRepository
 * @package Framework\Base\Repository
 */
abstract class BrunoRepository implements BrunoRepositoryInterface
{
    use ApplicationAwareTrait;

    /**
     * @var DatabaseAdapterInterface|null
     */
    private $adapter = null;

    /**
     * @var RepositoryInterface|null
     */
    private $repositoryManager = null;

    /**
     * @var array
     */
    private $modelAttributesDefinition = [];

    /**
     * @param DatabaseAdapterInterface $adapter
     * @return $this
     */
    public function setDatabaseAdapter(DatabaseAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * @return DatabaseAdapterInterface|null
     */
    public function getDatabaseAdapter()
    {
        return $this->getRepositoryManager()->getDatabaseAdapter();
    }

    /**
     * @param RepositoryInterface $repositoryManager
     * @return $this
     */
    public function setRepositoryManager(RepositoryInterface $repositoryManager)
    {
        $this->repositoryManager = $repositoryManager;

        return $this;
    }

    /**
     * @return RepositoryInterface|null
     */
    public function getRepositoryManager()
    {
        return $this->repositoryManager;
    }

    /**
     * @return string
     */
    public function getModelClassName()
    {
        $repositoryClass = get_class($this);

        return $this->getRepositoryManager()
            ->getModelClass($repositoryClass);
    }

    /**
     * @param $identifier
     * @return BrunoInterface|null
     */
    public function loadOne($identifier)
    {
        $modelClass = $this->getModelClassName();

        /* @var BrunoInterface $model */
        $model = new $modelClass();

        $query = $this->createNewQueryForModel($model);

        $data = $this->getDatabaseAdapter()
            ->loadOne($query);

        // Return null if no document found
        if ($data === null) {
            return $data;
        }

        $attributes = $data->getArrayCopy();

        $model->setAttributes($attributes);
        $model->setDatabaseAttributes($attributes);
        $model->setIsNew(false);

        return $model;
    }

    /**
     * @return BrunoInterface[]
     */
    public function loadMultiple()
    {
        $modelClass = $this->getModelClassName();

        /* @var BrunoInterface $model */
        $model = new $modelClass();

        $query = $this->createNewQueryForModel($model);

        $data = $this->getDatabaseAdapter()
            ->loadMultiple($query);

        $out = [];
        foreach ($data as $attributes) {
            $attributes = $attributes->getArrayCopy();

            $modelAttributesDefinition = $this->getModelAttributesDefinition();

            $model = new $modelClass();
            $model->defineModelAttributes($modelAttributesDefinition)
                ->setApplication($this->getApplication())
                ->setAttributes($attributes)
                ->setDatabaseAttributes($attributes)
                ->setIsNew(false);

            $out[] = $model;
        }

        return $out;
    }

    /**
     * @return array
     */
    public function getModelAttributesDefinition()
    {
        return $this->modelAttributesDefinition;
    }

    /**
     * @param BrunoInterface $bruno
     * @return BrunoInterface
     */
    public function save(BrunoInterface $bruno)
    {
        // TODO: Implement save() method, let bruno use that
        throw new \RuntimeException('Not implemented');
        return $bruno;
    }

    /**
     * @param BrunoInterface $bruno
     * @return DatabaseQueryInterface
     */
    protected function createNewQueryForModel(BrunoInterface $bruno)
    {
        $query = new MongoQuery();
        $query->setDatabase($bruno->getDatabase());
        $query->setCollection($bruno->getCollection());
        return $query;
    }
}
