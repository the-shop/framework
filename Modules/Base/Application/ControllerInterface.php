<?php

namespace Framework\Base\Application;

/**
 * Interface ControllerInterface
 * @package Framework\Http\Request
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
     * @return \Framework\Base\Model\RepositoryManagerInterface
     */
    public function getRepositoryManager();

    /**
     * @param $fullyQualifiedClassName
     * @return \Framework\Base\Model\BrunoRepositoryInterface
     */
    public function getRepository($fullyQualifiedClassName);
}
