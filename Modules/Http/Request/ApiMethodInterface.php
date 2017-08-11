<?php

namespace Framework\Http\Request;

use Framework\Application\Base\BaseApplication;

/**
 * Interface ApiMethodInterface
 * @package Framework\Http\Request
 */
interface ApiMethodInterface
{
    /**
     * @return mixed
     */
    public function handle();

    /**
     * @param BaseApplication $application
     * @return $this
     */
    public function setApplication(BaseApplication $application);

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
