<?php

namespace Application\Test\Application;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class DummyCurlResponse implements ResponseInterface
{
    public $contents;

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return 'Not implemented';
    }

    /**
     * @param int    $code
     * @param string $reasonPhrase
     *
     * @return mixed
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        return 'Not implemented';
    }

    /**
     * @return mixed
     */
    public function getReasonPhrase()
    {
        return 'Not implemented';
    }

    /**
     * @return mixed
     */
    public function getProtocolVersion()
    {
        return 'Not implemented';
    }

    /**
     * @param string $version
     *
     * @return mixed
     */
    public function withProtocolVersion($version)
    {
        return 'Not implemented';
    }

    /**
     * @return mixed
     */
    public function getHeaders()
    {
        return 'Not implemented';
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function hasHeader($name)
    {
        return 'Not implemented';
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getHeader($name)
    {
        return 'Not implemented';
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getHeaderLine($name)
    {
        return 'Not implemented';
    }

    /**
     * @param string          $name
     * @param string|string[] $value
     *
     * @return mixed
     */
    public function withHeader($name, $value)
    {
        return 'Not implemented';
    }

    /**
     * @param string          $name
     * @param string|string[] $value
     *
     * @return mixed
     */
    public function withAddedHeader($name, $value)
    {
        return 'Not implemented';
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function withoutHeader($name)
    {
        return 'Not implemented';
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this;
    }

    /**
     * @param \Psr\Http\Message\StreamInterface $body
     *
     * @return mixed
     */
    public function withBody(StreamInterface $body)
    {
        return 'Not implemented';
    }

    public function setContents($contents)
    {
        $this->contents = $contents;

        return $this;
    }

    public function getContents()
    {
        return $this->contents;
    }
}
