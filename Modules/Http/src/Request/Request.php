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
     * @var array
     */
    private $cookies = [];

    /**
     * @var string
     */
    private $requestMethod = 'GET';

    /**
     * @var string|null
     */
    private $requestUri = null;

    /**
     * @var null|string
     */
    private $clientIp = null;

    /**
     * @param array $get
     * @return $this
     */
    public function setQuery(array $get = [])
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
    public function setPost(array $post = [])
    {
        $this->postParams = $post;

        return $this;
    }

    /**
     * @param array $cookies
     *
     * @return $this
     */
    public function setCookies(array $cookies = [])
    {
        $this->cookies = $cookies;

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
    public function setFiles(array $files = [])
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
     * @return array
     */
    public function getCookies()
    {
        return $this->cookies;
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
        return $this->requestMethod;
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

        $requestMethod = isset($serverInformationMap['REQUEST_METHOD']) === true
            ? strtoupper($serverInformationMap['REQUEST_METHOD']) : 'GET';

        $this->setMethod($requestMethod);

        return $this;
    }

    /**
     * @return array
     */
    public function getServer()
    {
        return $this->serverInformation;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        if (function_exists('getallheaders') === true) {
            if (getallheaders() !== false) {
                return getallheaders();
            }
        }
        return [];
    }

    /**
     * @param string $ip
     * @return RequestInterface
     */
    public function setClientIp(string $ip)
    {
        $this->clientIp = $ip;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getClientIp()
    {
        return $this->clientIp;
    }
}
