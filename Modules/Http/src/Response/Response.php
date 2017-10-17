<?php

namespace Framework\Http\Response;

use Framework\Base\Response\ResponseInterface;

/**
 * Class Response
 * @package Framework\Http\Response
 */
class Response implements ResponseInterface
{
    /**
     * @var mixed
     */
    private $body = null;

    /**
     * @var int
     */
    private $code = 200;

    /**
     * @var array
     */
    private $headers = [];

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function addHeaders(array $headers = [])
    {
        foreach ($headers as $key => $value) {
            $this->setHeader($key, $value);
        }

        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function addHeader(string $key, string $value)
    {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * @param $responseBody
     * @return $this
     */
    public function setBody($responseBody)
    {
        $this->body = $responseBody;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param int $code
     * @return $this
     */
    public function setCode(int $code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }
}
