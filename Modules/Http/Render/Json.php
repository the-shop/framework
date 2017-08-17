<?php

namespace Framework\Http\Render;

use Framework\Base\Render\Render;
use Framework\Base\Response\ResponseInterface;

/**
 * Class Json
 * @package Framework\Base\Render
 */
class Json extends Render
{
    /**
     * @param ResponseInterface $response
     * @return mixed
     */
    public function render(ResponseInterface $response)
    {
        $responseBody = $response->getBody();

        http_response_code($response->getCode());

        header('Content-type: application/json');

        $rendered = json_encode($responseBody);

        echo $rendered;

        return $rendered;
    }
}
