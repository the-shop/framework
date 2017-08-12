<?php

namespace Framework\Base\Render;

use Framework\Http\Response\ResponseInterface;

class Json extends Render
{
    public function render(ResponseInterface $response)
    {
        $responseBody = $response->getBody();

        echo json_encode($responseBody);
    }
}
