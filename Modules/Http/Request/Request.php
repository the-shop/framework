<?php

namespace Framework\Http\Request;

use Framework\Base\Request\RequestInterface;

/**
 * Class Request
 * @package Framework\Http\Request
 */
class Request implements RequestInterface
{
    /**
     * @var array
     */
    private $queryParams = [];

    /**
     * @var array
     */
    private $postParams = [];

    /**
     * @var array
     */
    private $fileParams = [];

    /**
     * @var array
     */
    private $serverInformation = [];

    /**
     * @param array $get
     * @return $this
     */
    public function setQuery($get = [])
    {
        $this->queryParams = $get;
        return $this;
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return $this->queryParams;
    }

    /**
     * @param array $post
     * @return $this
     */
    public function setPost($post = [])
    {
        $this->postParams = $post;
        return $this;
    }

    /**
     * @return array
     */
    public function getPost()
    {
        return $this->postParams;
    }

    /**
     * @param array $files
     * @return $this
     */
    public function setFiles($files = [])
    {
        $this->fileParams = $files;
        return $this;
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->fileParams;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return isset($this->serverInformation['REQUEST_METHOD']) ? $this->serverInformation['REQUEST_METHOD'] : 'get';
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return isset($this->serverInformation['REQUEST_URI']) ? $this->serverInformation['REQUEST_URI'] : null;
    }

    /**
     * @param array $serverInformationMap
     * @return $this
     */
    public function setServer(array $serverInformationMap = [])
    {
        $this->serverInformation = $serverInformationMap;

        return $this;
    }
}
