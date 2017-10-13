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
     * @return array
     */
    public function getHeaders(): array;

    /**
     * @param string $key
     * @param string $value
     * @return ResponseInterface
     */
    public function setHeader(string $key, string $value);

    /**
     * @param array $headers
     * @return ResponseInterface
     */
    public function setHeaders(array $headers = []);
}
