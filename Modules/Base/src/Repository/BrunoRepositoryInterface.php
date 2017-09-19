<?php

namespace Framework\Base\Repository;

use Framework\Base\Application\ApplicationAwareInterface;
use Framework\Base\Database\DatabaseAdapterInterface;
use Framework\Base\Manager\RepositoryManagerInterface;
use Framework\Base\Model\BrunoInterface;

/**
 * Interface BrunoRepositoryInterface
 * @package Framework\Base\Repository
 */
interface BrunoRepositoryInterface extends ApplicationAwareInterface
{
    /**
     * @param string $resourceName
     * @return BrunoRepositoryInterface;
     */
    public function setResourceName(string $resourceName);

    /**
     * @return string
     */
    public function getResourceName();

    /**
     * @return DatabaseAdapterInterface|null
     */
    public function getDatabaseAdapters();

    /**
     * @param RepositoryManagerInterface $repositoryManager
     * @return $this
     */
    public function setRepositoryManager(RepositoryManagerInterface $repositoryManager);

    /**
     * @return mixed
     */
    public function newModel();

    /**
     * @param $identifier
     * @return BrunoInterface|null
     */
    public function loadOne($identifier);

    /**
     * @return BrunoInterface[]
     */
    public function loadMultiple();

    /**
     * @param BrunoInterface $bruno
     * @return BrunoInterface
     */
    public function save(BrunoInterface $bruno);
}
