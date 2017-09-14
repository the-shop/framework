<?php

namespace Framework\Base\Repository;

use Framework\Base\Application\ApplicationAwareTrait;
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
     * @var string
     */
    protected $resourceName = 'generic';

    /**
     * @var RepositoryInterface|null
     */
    private $repositoryManager = null;

    /**
     * @var array
     */
    private $modelAttributesDefinition = [];

    /**
     * @return mixed
     */
    public function getPrimaryAdapter()
    {
        return $this->getRepositoryManager()->getPrimaryAdapter($this->resourceName);
    }

    /**
     * @return mixed
     */
    public function getDatabaseAdapters()
    {
        return $this->getRepositoryManager()->getModelAdapters($this->resourceName);
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
     * @return BrunoInterface
     */
    public function newModel()
    {
        $modelClass = $this->getModelClassName();

        $modelAttributesDefinition = $this->getModelAttributesDefinition();

        /* @var BrunoInterface $model */
        $model = new $modelClass();

        $model->defineModelAttributes($modelAttributesDefinition)
            ->setApplication($this->getApplication());

        return $model;
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
        /* @var BrunoInterface $model */
        $model = $this->newModel();

        $adapter = $this->getPrimaryAdapter();

        $query = $this->createNewQueryForModel($model);

        $data = $adapter
            ->loadOne($query);


        if ($data === null) {
            return null;
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
        /* @var BrunoInterface $model */
        $model = $this->newModel();

        $adapter = $this->getPrimaryAdapter();

        $out = [];

        $query = $this->createNewQueryForModel($model);

        $data = $adapter
            ->loadMultiple($query);

        foreach ($data as $attributes) {
            $attributes = $attributes->getArrayCopy();

            $modelAttributesDefinition = $this->getModelAttributesDefinition();

            $model = $this->newModel();
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
