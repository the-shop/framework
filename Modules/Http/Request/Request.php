<?php

namespace Framework\Http\Request;

/**
 * Class Request
 * @package Framework\Http\Request
 */
class Request
{
    /**
     * @var array
     */
    private $getParams = [];

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
     * @param array $get
     * @return $this
     */
    public function setGet($get = []) {
        $this->getParams = $get;
        return $this;
    }

    /**
     * @param array $post
     * @return $this
     */
    public function setPost($post = []) {
        $this->postParams = $post;
        return $this;
    }

    /**
     * @param array $files
     * @return $this
     */
    public function setFiles($files = []) {
        $this->fileParams = $files;
        return $this;
    }

    /**
     * @param $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = strtolower($method);
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
}
