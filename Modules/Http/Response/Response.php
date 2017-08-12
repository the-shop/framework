<?php

namespace Framework\Http\Response;

class Response implements ResponseInterface
{
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

    public function getBody()
    {
        return $this->body;
    }
}
