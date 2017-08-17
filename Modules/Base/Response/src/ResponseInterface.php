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
}
