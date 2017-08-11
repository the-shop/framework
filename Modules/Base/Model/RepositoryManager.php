<?php

namespace Framework\Base\Model;
use Framework\Base\Database\DatabaseAdapterInterface;
use Framework\Base\Database\MongoAdapter;

/**
 * Class RepositoryManager
 * @package Framework\Base\Model
 */
class RepositoryManager implements RepositoryManagerInterface
{
    /**
     * @var [DatabaseAdapterInterface]
     */
    private $registeredRepositories = [];

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
     */
    public function setDatabaseAdapter(DatabaseAdapterInterface $adapter)
    {
        $this->databaseAdapter = $adapter;
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

        $adapter = new $this->registeredRepositories[$fullyQualifiedClassName]();
        $adapter->setDatabaseAdapter($this->getDatabaseAdapter());

        return $adapter;
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
}
