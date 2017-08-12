<?php

namespace Framework\Base\Application;

/**
 * Interface ApplicationInterface
 * @package Framework\Base\Application
 */
interface ApplicationInterface
{
    /**
     * @return mixed
     */
    public function run();

    /**
     * @return \Framework\Base\Manager\RepositoryInterface|null
     */
    public function getRepositoryManager();

    /**
     * @return \Framework\Http\Request\Request
     */
    public function getRequest();
}
