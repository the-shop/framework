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

        $rendered = json_encode($responseBody);

        echo $rendered;

        return $rendered;
    }
}
