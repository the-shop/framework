<?php

namespace Framework\Base\Model;

use Framework\Base\Database\DatabaseAdapterInterface;

/**
 * Interface RepositoryManagerInterface
 * @package Framework\Base\Model
 */
interface RepositoryManagerInterface
{
    /**
     * @param string $fullyQualifiedClassName
     * @return BrunoRepositoryInterface
     */
    public function getRepository(string $fullyQualifiedClassName = '');

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

    public function setDatabaseAdapter(DatabaseAdapterInterface $adapter);
}
