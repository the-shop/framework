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
     * @return \Framework\Base\Model\RepositoryManagerInterface|null
     */
    public function getRepositoryManager();

    /**
     * @return \Framework\Http\Request\Request
     */
    public function getRequest();
}