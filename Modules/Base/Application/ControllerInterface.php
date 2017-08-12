<?php

namespace Framework\Base\Application;

/**
 * Interface ControllerInterface
 * @package Framework\Base\Application
 */
interface ControllerInterface
{
    /**
     * @return mixed
     */
    public function handle();

    /**
     * @param ApplicationInterface $application
     * @return mixed
     */
    public function setApplication(ApplicationInterface $application);

    /**
     * @return array
     */
    public function getRegisteredRequestMethods();

    /**
     * @return array
     */
    public function getRegisteredRequestRoutes();

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
