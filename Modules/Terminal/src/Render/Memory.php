<?php

namespace Framework\Terminal\Render;

use Framework\Base\Render\Render;
use Framework\Base\Response\ResponseInterface;

/**
 * Class Memory
 * @package Framework\Terminal\Render
 */
class Memory extends Render
{
    private $response = null;

    /**
     * @param ResponseInterface $response
     * @return mixed
     */
    public function render(ResponseInterface $response)
    {
        $this->response = $response;

        return $this->response;
    }
}
