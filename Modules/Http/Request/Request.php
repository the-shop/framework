<?php

namespace Framework\Http\Request;

use Framework\Base\Module\ModuleInterface;

class Request implements ModuleInterface
{
    private $getParams = [];
    private $postParams = [];
    private $fileParams = [];
    private $method = null;

    public function bootstrap()
    {
        // TODO: Implement bootstrap() method.
    }

    public function setGet($get = []) {
        $this->getParams = $get;
        return $this;
    }

    public function setPost($post = []) {
        $this->postParams = $post;
        return $this;
    }

    public function setFiles($files = []) {
        $this->fileParams = $files;
        return $this;
    }

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