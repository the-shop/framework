<?php

namespace Modules\Http\Response;

use Modules\Base\JsonOutput\JsonOutput;
use Modules\Base\Module\ModuleInterface;

class Response implements ModuleInterface
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