<?php

namespace Framework\Http\Response;

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
}
