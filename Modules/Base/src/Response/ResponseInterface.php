<?php

namespace Framework\Base\Response;

/**
 * Interface ResponseInterface
 * @package Framework\Base\Response
 */
interface ResponseInterface
{
    /**
     * @param $responseBody
     * @return ResponseInterface
     */
    public function setBody($responseBody);

    /**
     * @return mixed
     */
    public function getBody();

    /**
     * @param int $code
     * @return mixed
     */
    public function setCode(int $code);

    /**
     * @return mixed
     */
    public function getCode();

    /**
     * @param array $headers
     *
     * @return \Framework\Base\Response\ResponseInterface
     */
    public function addHeaders(array $headers): ResponseInterface;

    /**
     * @param string $headerName
     * @param string $headerValue
     *
     * @return \Framework\Base\Response\ResponseInterface
     */
    public function addHeader(string $headerName, string $headerValue): ResponseInterface;

    /**
     * @return array
     */
    public function getHeaders(): array;
}
