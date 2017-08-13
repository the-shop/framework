<?php

namespace Framework\Http\Controller;

use Framework\Base\Application\BaseController;

/**
 * Class Http
 * @package Framework\Http\Controller
 */
class Http extends BaseController
{
    /**
     * @return array
     */
    public function getPost()
    {
        return $this->getApplication()
            ->getRequest()
            ->getPost();
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return $this->getApplication()
            ->getRequest()
            ->getQuery();
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->getApplication()
            ->getRequest()
            ->getFiles();
    }
}
