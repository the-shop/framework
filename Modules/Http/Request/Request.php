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
     * @var null
     */
    private $method = null;

    /**
     * @var string
     */
    private $uri = null;

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
     * @param $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        if ($this->method === null) {
            throw new \RuntimeException('Invalid flow.');
        }

        return $this->method;
    }

    /**
     * @param $uri
     * @return $this
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }
}
