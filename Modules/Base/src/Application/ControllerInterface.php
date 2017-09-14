<?php

namespace Framework\Base\Application;

/**
 * Interface ControllerInterface
 * @package Framework\Base\Application
 */
interface ControllerInterface extends ApplicationAwareInterface
{
    /**
     * @return \Framework\Base\Manager\RepositoryManagerInterface
     */
    public function getRepositoryManager();

    /**
     * @param $fullyQualifiedClassName
     * @return \Framework\Base\Repository\BrunoRepositoryInterface
     */
    public function getRepository($fullyQualifiedClassName);
}
