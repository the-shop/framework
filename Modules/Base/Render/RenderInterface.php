<?php

namespace Framework\Base\Render;

use Framework\Base\Response\ResponseInterface;

/**
 * Interface RenderInterface
 * @package Framework\Base\Render
 */
interface RenderInterface
{
    /**
     * @param ResponseInterface $response
     * @return mixed
     */
    public function render(ResponseInterface $response);
}
