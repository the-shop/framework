<?php

namespace Framework\Base\Render;

use Framework\Http\Response\ResponseInterface;

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

        http_response_code($response->getHttpCode());

        header('Content-type: application/json');

        $rendered = json_encode($responseBody);

        echo $rendered;

        return $rendered;
    }
}
