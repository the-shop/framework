<?php

namespace Framework\Base\Manager;

use Framework\Base\Database\DatabaseAdapterInterface;
use Framework\Base\Repository\BrunoRepositoryInterface;

/**
 * Interface RepositoryInterface
 * @package Framework\Base\Manager
 */
interface RepositoryInterface
{
    /**
     * @param string $fullyQualifiedClassName
     * @return BrunoRepositoryInterface
     */
    public function getRepository(string $fullyQualifiedClassName = '');

    /**
     * @param string $resourceName
     * @return mixed
     */
    public function getRepositoryFromResourceName(string $resourceName);

    /**
     * @param string $fullyQualifiedClassName
     * @return $this
     */
    public function registerRepository(string $fullyQualifiedClassName = '');

    /**
     * @param array $fullyQualifiedClassNames
     * @return $this
     */
    public function registerRepositories(array $fullyQualifiedClassNames = []);

    /**
     * @param array $resourcesMap
     * @return $this
     */
    public function registerResources(array $resourcesMap = []);

    /**
     * @param array $modelFieldsMap
     * @return mixed
     */
    public function registerModelFields(array $modelFieldsMap = []);

    /**
     * @param DatabaseAdapterInterface $adapter
     * @return $this
     */
    public function setDatabaseAdapter(DatabaseAdapterInterface $adapter);

    /**
     * @return DatabaseAdapterInterface|null
     */
    public function getDatabaseAdapter();

    /**
     * @param string $repositoryClass
     * @return mixed
     */
    public function getModelClass(string $repositoryClass);

    /**
     * @param string $modelClassName
     * @param DatabaseAdapterInterface $adapter
     * @return $this
     */
    public function addModelAdapter(string $modelClassName, DatabaseAdapterInterface $adapter);

    /**
     * @param string $modelClassName
     * @return mixed
     */
    public function getModelAdapters(string $modelClassName);

    /**
     * @param array $modelsConfigs
     *
     * @return $this
     */
    public function addAuthenticatableModels(array $modelsConfigs);

    /**
     * @param string $modelName
     * @param array  $params
     *
     * @return $this
     */
    public function addAuthenticatableModel(string $modelName, array $params);

    /**
     * @return array
     */
    public function getAuthenticatableModels();
}
