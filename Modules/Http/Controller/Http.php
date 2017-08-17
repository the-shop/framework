<?php

namespace Framework\Http\Controller;

use Framework\Base\Application\BaseController;
use Framework\Http\Request\Request;

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
        return $this->getRequest()
            ->getPost();
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return $this->getRequest()
            ->getQuery();
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->getRequest()
            ->getFiles();
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        /* @var Request $request */
        $request = $this->getApplication()
            ->getRequest();

        return $request;
    }
}
