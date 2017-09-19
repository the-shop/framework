<?php

namespace Framework\Http\Request;

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
     * @var string
     */
    private $requestMethod = 'get';

    /**
     * @var string|null
     */
    private $requestUri = null;

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
     * @param string $method
     * @return $this
     */
    public function setMethod(string $method)
    {
        $this->requestMethod = strtoupper($method);

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        $serverInfo = $this->serverInformation;
        return isset($serverInfo['REQUEST_METHOD']) === true
            ? strtoupper($serverInfo['REQUEST_METHOD']) : $this->requestMethod;
    }

    /**
     * @param string $uri
     * @return $this
     */
    public function setUri(string $uri)
    {
        // Normalize $uri, prepend with slash if not there
        if (strlen($uri) > 0 && $uri[0] !== '/') {
            $uri = '/' . $uri;
        }

        // Strip query string (?foo=bar)
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        $this->requestUri = $uri;

        return $this;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->requestUri;
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
