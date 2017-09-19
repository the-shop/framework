<?php

namespace Framework\Base\Manager;

use Framework\Base\Application\ApplicationAwareInterface;
use Framework\Base\Application\ApplicationAwareTrait;
use Framework\Base\Database\DatabaseAdapterInterface;
use Framework\Base\Repository\BrunoRepositoryInterface;

/**
 * Class RepositoryManager
 * @package Framework\Base\Manager
 */
class RepositoryManager implements RepositoryManagerInterface, ApplicationAwareInterface
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
     * @var array
     */
    private $modelAdapters = [];

    /**
     * @var null
     */
    private $primaryAdapters = [];

    /**
     * @var array
     */
    private $authenticatableModels = [];

    /**
     * @param string $fullyQualifiedClassName
     *
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
     *
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
        $repository->setRepositoryManager($this)
                   ->setResourceName($resourceName)
                   ->setApplication($this->getApplication());

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
     *
     * @return $this
     */
    public function registerRepository(string $fullyQualifiedClassName = '')
    {
        /**@todo unify implementation with `registerRepositories()` */

        array_push($this->registeredRepositories, $fullyQualifiedClassName);

        /**@todo implement `array_unique()` differently
         *          since its not meant to work on multidimensional arrays
         *          and it has big impact on performance
         */
        $this->registeredRepositories = array_unique($this->registeredRepositories, SORT_REGULAR);

        return $this;
    }

    /**
     * @param array $fullyQualifiedClassNames modelClass => repoClass
     *
     * @return $this
     */
    public function registerRepositories(array $fullyQualifiedClassNames = [])
    {
        $this->registeredRepositories = array_merge($this->registeredRepositories, $fullyQualifiedClassNames);

        /**@todo implement `array_unique()` differently
         *          since its not meant to work on multidimensional arrays
         *          and it has big impact on performance
         */
//        $this->registeredRepositories = array_unique($this->registeredRepositories, SORT_REGULAR);

        return $this;
    }

    /**
     * @param array $modelClassNameToCollection
     *
     * @return $this
     */
    public function registerModelsToCollection(array $modelClassNameToCollection)
    {
        $this->modelsToCollection = $modelClassNameToCollection;

        return $this;
    }

    /**
     * @param array $resourcesMap
     *
     * @return $this
     */
    public function registerResources(array $resourcesMap = [])
    {
        $this->registeredResources = array_merge_recursive($this->registeredResources, $resourcesMap);

        /**@todo implement `array_unique()` differently
         *          since its not meant to work on multidimensional arrays
         *          and it has big impact on performance
         */
//        $this->registeredRepositories = array_unique($this->registeredRepositories, SORT_REGULAR);

        foreach ($resourcesMap as $resourceName => $repository) {
            if (isset($this->primaryAdapters[$resourceName]) === false) {
                $adapters = $this->getModelAdapters($resourceName);
                $this->setPrimaryAdapter($resourceName, reset($adapters));
            }
        }

        return $this;
    }

    /**
     * @param array $modelFieldsMap
     *
     * @return $this
     */
    public function registerModelFields(array $modelFieldsMap = [])
    {
        $this->registeredModelFields = array_merge($this->registeredModelFields, $modelFieldsMap);

        /**@todo implement `array_unique()` differently
         *          since its not meant to work on multidimensional arrays
         *          and it has big impact on performance
         */
//        $this->registeredModelFields = array_unique($this->registeredModelFields, SORT_REGULAR);

        return $this;
    }

    /**
     * @param string $resourceName
     *
     * @return array
     * @throws \RuntimeException
     */
    public function getRegisteredModelFields(string $resourceName)
    {
        if (isset($this->registeredModelFields[$resourceName]) === false) {
            throw new \RuntimeException('Model fields definition missing for model name: ' . $resourceName);
        }

        return $this->registeredModelFields[$resourceName];
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
     * @param string $modelClassName
     * @param DatabaseAdapterInterface $adapter
     * @return $this
     */
    public function setPrimaryAdapter(string $modelClassName, DatabaseAdapterInterface $adapter)
    {
        $this->primaryAdapters[$modelClassName] = $adapter;

        return $this;
    }

    /**
     * @param string $modelClassName
     * @return mixed
     */
    public function getPrimaryAdapter(string $modelClassName)
    {
        if (isset($this->primaryAdapters[$modelClassName]) === false) {
            throw new \RuntimeException('No registered primary adapter for ' . $modelClassName);
        }

        return $this->primaryAdapters[$modelClassName];
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
