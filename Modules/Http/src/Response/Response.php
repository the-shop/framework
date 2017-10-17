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

    /**
     * @param array $headers
     *
     * @return ResponseInterface
     */
    public function addHeaders(array $headers): ResponseInterface
    {
        foreach ($headers as $name => $value) {
            $this->addHeader($name, $value);
        }

        return $this;
    }

    /**
     * @param string $headerName
     * @param string $headerValue
     *
     * @return \Framework\Base\Response\ResponseInterface
     */
    public function addHeader(string $headerName, string $headerValue): ResponseInterface
    {
        $this->headers[$headerName] = $headerValue;

        return $this;
    }
}
