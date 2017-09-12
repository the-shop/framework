<?php

namespace Framework\Base\Repository;

use Framework\Base\Application\ApplicationAwareInterface;
use Framework\Base\Database\DatabaseAdapterInterface;
use Framework\Base\Manager\RepositoryInterface;
use Framework\Base\Model\BrunoInterface;

/**
 * Interface BrunoRepositoryInterface
 * @package Framework\Base\Repository
 */
interface BrunoRepositoryInterface extends ApplicationAwareInterface
{
    /**
     * @param DatabaseAdapterInterface $adapter
     * @return mixed
     */
    public function setDatabaseAdapter(DatabaseAdapterInterface $adapter);

    /**
     * @return DatabaseAdapterInterface|null
     */
    public function getDatabaseAdapter();

    /**
     * @param RepositoryInterface $repositoryManager
     * @return $this
     */
    public function setRepositoryManager(RepositoryInterface $repositoryManager);

    /**
     * @param $identifier
     * @return BrunoInterface|null
     */
    public function loadOne($identifier);

    /**
     * @return [BrunoInterface]
     */
    public function loadMultiple();

    /**
     * @param BrunoInterface $bruno
     * @return BrunoInterface
     */
    public function save(BrunoInterface $bruno);
}
