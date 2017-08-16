<?php

namespace Framework\Http\Response;

/**
 * Interface ResponseInterface
 * @package Framework\Http\Response
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
    public function setHttpCode(int $code);

    /**
     * @return mixed
     */
    public function getHttpCode();
}
