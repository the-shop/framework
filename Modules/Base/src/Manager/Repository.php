<?php

namespace Framework\Base\Manager;

use Framework\Base\Application\ApplicationAwareInterface;
use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Database\DatabaseAdapterInterface;
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
     * @var array
     */
    private $modelsToCollection = [];

    /**
     * @var [string]
     */
    private $registeredModelFields = [];

    /**
     * @var DatabaseAdapterInterface|null
     */
    private $databaseAdapter = null;

    /**
     * @var array
     */
    private $modelAdapters = [];

    /**
     * RepositoryManager constructor.
     * @param DatabaseAdapterInterface $adapter
     */
    public function __construct(DatabaseAdapterInterface $adapter)
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
        $repository->setRepositoryManager($this)
            ->setApplication($this->getApplication());

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
        $repository->setRepositoryManager($this)
            ->setApplication($this->getApplication());

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
     * @param array $modelClassNameToCollection
     * @return $this
     */
    public function registerModelsToCollection(array $modelClassNameToCollection)
    {
        $this->modelsToCollection = $modelClassNameToCollection;

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

    /**
     * @param array $modelFieldsMap
     * @return $this
     */
    public function registerModelFields(array $modelFieldsMap = [])
    {
        $this->registeredModelFields = array_merge($this->registeredModelFields, $modelFieldsMap);

        $this->registeredModelFields = array_unique($this->registeredModelFields);

        return $this;
    }

    /**
     * @param string $resourceName
     * @return array
     */
    public function getRegisteredModelFields(string $resourceName)
    {
        if (isset($this->registeredModelFields[$resourceName]) === false) {
            throw new \RuntimeException('Model fields definition missing for model name: ' . $resourceName);
        }

        return $this->registeredModelFields[$resourceName];
    }

    public function addModelAdapter(string $modelClassName, DatabaseAdapterInterface $adapter)
    {
        $this->modelAdapters[$modelClassName][] = $adapter;

        return $this;
    }

    /**
     * @param string $modelClassName
     * @return array
     */
    public function getModelAdapters(string $modelClassName)
    {
        if (isset($this->modelAdapters[$modelClassName]) === false) {
            throw new RuntimeException('No registered adapters for ' . $modelClassName);
        }
        return $this->modelAdapters[$modelClassName];
    }
}
