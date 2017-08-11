<?php

namespace Framework\Http\Response;

use Framework\Base\JsonOutput\JsonOutput;
use Framework\Base\Module\BaseModule;

class Response extends BaseModule
{
    private $body = '';
    private $response = null;

    public function __construct($response)
    {
        $this->response = $response;
    }

    public function bootstrap()
    {
        // TODO: Implement bootstrap() method.
    }

    public function output()
    {
        $output = new JsonOutput();
        $output->output($this->formatBody());
    }

    private function formatBody()
    {
        $this->body = $this->response;

        return $this->body;
    }
}