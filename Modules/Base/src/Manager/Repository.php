<?php

namespace Framework\Base\Manager;

use Framework\Base\Application\ApplicationAwareInterface;
use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Database\DatabaseAdapterInterface;
use Framework\Base\Database\MongoAdapter;
use Framework\Base\Repository\BrunoRepositoryInterface;
use MongoDB\Exception\RuntimeException;

/**
 * Class Repository
 * @package Framework\Base\Manager
 */
class Repository implements RepositoryInterface, ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    /**
     * @var [string]
     */
    private $registeredRepositories = [];

    /**
     * @var [string]
     */
    private $registeredResources = [];

    /**
     * @var DatabaseAdapterInterface|null
     */
    private $databaseAdapter = null;

    /**
     * RepositoryManager constructor.
     * @param MongoAdapter $adapter
     */
    public function __construct(MongoAdapter $adapter)
    {
        $this->setDatabaseAdapter($adapter);
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
     * @return DatabaseAdapterInterface|null
     */
    public function getDatabaseAdapter()
    {
        return $this->databaseAdapter;
    }

    /**
     * @param string $fullyQualifiedClassName
     * @return BrunoRepositoryInterface
     */
    public function getRepository(string $fullyQualifiedClassName = '')
    {
        if (!class_exists($fullyQualifiedClassName)) {
            throw new \RuntimeException('Model ' . $fullyQualifiedClassName . ' is not registered');
        }

        $repositoryClass = $this->registeredRepositories[$fullyQualifiedClassName];
        /* @var BrunoRepositoryInterface $repository */
        $repository = new $repositoryClass();
        $repository->setRepositoryManager($this);

        return $repository;
    }

    /**
     * @param string $resourceName
     * @return BrunoRepositoryInterface
     */
    public function getRepositoryFromResourceName(string $resourceName)
    {
        if (array_key_exists($resourceName, $this->registeredResources) === false) {
            throw new \RuntimeException('Resource "' . $resourceName . '" not registered in Framework\Base\Manager\Repository');
        }

        $repositoryClass = $this->registeredResources[$resourceName];
        /* @var BrunoRepositoryInterface $repository */
        $repository = new $repositoryClass();
        $repository->setRepositoryManager($this);

        return $repository;
    }

    public function getModelClass(string $repositoryClass)
    {
        $foundClass = null;
        foreach ($this->registeredRepositories as $modelClass => $repoClass) {
            if ($repositoryClass === $repoClass) {
                $foundClass = $modelClass;
                break;
            }
        }

        if ($foundClass === null) {
            throw new RuntimeException('Model class not registered for ' . $repositoryClass);
        }

        return $foundClass;
    }

    /**
     * @param string $fullyQualifiedClassName
     * @return $this
     */
    public function registerRepository(string $fullyQualifiedClassName = '')
    {
        array_push($this->registeredRepositories, $fullyQualifiedClassName);

        $this->registeredRepositories = array_unique($this->registeredRepositories);

        return $this;
    }

    /**
     * @param array $fullyQualifiedClassNames
     * @return $this
     */
    public function registerRepositories(array $fullyQualifiedClassNames = [])
    {
        $this->registeredRepositories = array_merge($this->registeredRepositories, $fullyQualifiedClassNames);

        $this->registeredRepositories = array_unique($this->registeredRepositories);

        return $this;
    }

    /**
     * @param array $resourcesMap
     * @return $this
     */
    public function registerResources(array $resourcesMap = [])
    {
        $this->registeredResources = array_merge($this->registeredResources, $resourcesMap);

        $this->registeredResources = array_unique($this->registeredResources);

        return $this;
    }
}