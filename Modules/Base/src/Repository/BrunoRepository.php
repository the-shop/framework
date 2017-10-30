<?php

namespace Framework\Base\Repository;

use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Database\DatabaseQueryInterface;
use Framework\Base\Manager\RepositoryManagerInterface;
use Framework\Base\Model\BrunoInterface;

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
     * @var RepositoryManagerInterface|null
     */
    private $repositoryManager = null;

    /**
     * @var array
     */
    private $modelAttributesDefinition = [];

    /**
     * Sets `$resourceName` as the document collection
     *
     * @param string $resourceName
     * @return $this
     */
    public function setResourceName(string $resourceName)
    {
        $this->resourceName = $resourceName;

        return $this;
    }

    /**
     * @return string
     */
    public function getResourceName()
    {
        return $this->resourceName;
    }

    /**
     * @return \Framework\Base\Database\DatabaseAdapterInterface
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
        return $this->getRepositoryManager()
            ->getModelAdapters($this->resourceName);
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

    /**
     * @return BrunoInterface
     */
    public function newModel()
    {
        $modelClass = $this->getModelClassName();

        $modelAttributesDefinition = $this->getModelAttributesDefinition();

        /* @var BrunoInterface $model */
        $model = new $modelClass();

        $config = $this->getApplication()->getConfiguration();

        $model->defineModelAttributes($modelAttributesDefinition)
            ->setPrimaryKey($this->getModelPrimaryKey())
            ->setCollection($this->resourceName)
            ->setApplication($this->getApplication())
            ->setDatabaseAddress($config->getPathValue('env.DATABASE_ADDRESS'))
            ->setDatabase($config->getPathValue('env.DATABASE_NAME'));

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
     * @return string
     */
    public function getModelPrimaryKey()
    {
        $modelAttDefinition = $this->getModelAttributesDefinition();

        foreach ($modelAttDefinition as $field => $definition) {
            if (array_key_exists('primaryKey', $definition) === true && $definition['primaryKey']
                === true) {
                return $field;
            }
        }
    }

    /**
     * @param $identifier
     * @return BrunoInterface|null
     */
    public function loadOne($identifier)
    {
        $model = $this->newModel();
        $adapter = $this->getPrimaryAdapter();

        if ($identifier instanceof DatabaseQueryInterface === false) {
            $query = $this->createNewQueryForModel($model);
            $query->addAndCondition($model->getPrimaryKey(), '=', $identifier);
        } else {
            $query = $identifier;
        }

        $attributes = $adapter
            ->loadOne($query);

        if ($attributes === null) {
            return null;
        }

        $model->disableFieldFilters();
        $model->setAttributes($attributes);
        $model->setDatabaseAttributes($attributes);
        $model->enableFieldFilters();
        $model->setIsNew(false);

        return $model;
    }

    /**
     * @param array $keyValues
     * Example:
     *  $keyValues = [
     *      'name' = 'Name',
     *      'role' = 'Role',
     *      'xp' = [
     *          '>',
     *          1000,
     *      ];
     *  ];
     *
     * @return \Framework\Base\Model\BrunoInterface|null
     */
    public function loadOneBy(array $keyValues = [])
    {
        $model = $this->newModel();
        $query = $this->createNewQueryForModel($model);

        foreach ($keyValues as $key => $identifier) {
            $condition = '=';
            if (is_array($identifier)) {
                list($condition, $value) = $identifier;
            } else {
                $value = $identifier;
            }
            $query->addAndCondition($key, $condition, $value);
        }

        $attributes = $this->getPrimaryAdapter()
            ->loadOne($query);

        if ($attributes === null) {
            return null;
        }

        $model->disableFieldFilters();
        $model->setAttributes($attributes);
        $model->setDatabaseAttributes($attributes);
        $model->enableFieldFilters();
        $model->setIsNew(false);

        return $model;
    }

    /**
     * @param $identifiers
     *
     * @return []|BrunoInterface[]
     */
    public function loadMultiple($identifiers = []): array
    {
        $model = $this->newModel();
        $adapter = $this->getPrimaryAdapter();
        $out = [];

        if ($identifiers instanceof DatabaseQueryInterface === false) {
            $query = $this->createNewQueryForModel($model);
            foreach ($identifiers as $identifier => $value) {
                $query->addAndCondition($identifier, '=', $value);
            }
        } else {
            $query = $identifiers;
        }

        $data = $adapter
            ->loadMultiple($query);

        foreach ($data as $attributes) {
            $model = $this->newModel();
            $model->disableFieldFilters()
                ->setAttributes($attributes)
                ->setDatabaseAttributes($attributes)
                ->enableFieldFilters()
                ->setIsNew(false);

            $out[$model->getId()] = $model;
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
     *
     * @return DatabaseQueryInterface
     */
    public function createNewQueryForModel(BrunoInterface $bruno): DatabaseQueryInterface
    {
        $query = $this->getPrimaryAdapter()
            ->newQuery();

        $query->setDatabase($bruno->getDatabase());
        $query->setCollection($bruno->getCollection());

        return $query;
    }
}
