<?php

namespace Framework\Base\Manager;

use Framework\Base\Application\ApplicationAwareInterface;
use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Database\DatabaseAdapterInterface;
use Framework\Base\Repository\BrunoRepositoryInterface;

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
     * @var array
     */
    private $authenticatableModels = [];

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
            throw new \RuntimeException('Resource "' . $resourceName
                                        . '" not registered in Framework\Base\Manager\Repository');
        }

        $repositoryClass = $this->registeredResources[$resourceName];
        /* @var BrunoRepositoryInterface $repository */
        $repository = new $repositoryClass();
        $repository->setRepositoryManager($this);

        return $repository;
    }

    /**
     * @param string $repositoryClass
     *
     * @return int|null|string
     * @throws \RuntimeException
     */
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
            throw new \RuntimeException('Model class not registered for ' . $repositoryClass);
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
     * @param string                                            $modelClassName
     * @param \Framework\Base\Database\DatabaseAdapterInterface $adapter
     *
     * @return $this
     */
    public function addModelAdapter(string $modelClassName, DatabaseAdapterInterface $adapter)
    {
        $this->modelAdapters[$modelClassName][] = $adapter;

        return $this;
    }

    /**
     * @param string $modelClassName
     *
     * @return DatabaseAdapterInterface[]
     * @throws \RuntimeException
     */
    public function getModelAdapters(string $modelClassName)
    {
        if (isset($this->modelAdapters[$modelClassName]) === false) {
            throw new \RuntimeException('No registered adapters for ' . $modelClassName);
        }

        return $this->modelAdapters[$modelClassName];
    }

    /**
     * @param array $modelsConfigs
     *
     * @return $this
     */
    public function addAuthenticatableModels(array $modelsConfigs = [])
    {
        foreach ($modelsConfigs as $modelName => $params) {
            $this->addAuthenticatableModel($modelName, $params);
        }

        return $this;
    }

    /**
     * @param string $modelName
     * @param array  $params
     *
     * @return $this
     */
    public function addAuthenticatableModel(string $modelName, array $params = [])
    {
        $this->authenticatableModels[$modelName] = $params;

        return $this;
    }

    /**
     * @return array
     */
    public function getAuthenticatableModels()
    {
        return $this->authenticatableModels;
    }
}
