<?php

namespace Framework\Base\Application;

/**
 * Interface ControllerInterface
 * @package Framework\Base\Application
 */
interface ControllerInterface
{
    /**
     * @param ApplicationInterface $application
     * @return mixed
     */
    public function setApplication(ApplicationInterface $application);

    /**
     * @return \Framework\Base\Manager\RepositoryInterface
     */
    public function getRepositoryManager();

    /**
     * @param $fullyQualifiedClassName
     * @return \Framework\Base\Repository\BrunoRepositoryInterface
     */
    public function getRepository($fullyQualifiedClassName);
}
