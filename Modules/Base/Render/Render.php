<?php

namespace Framework\Base\Render;

use Framework\Http\Response\ResponseInterface;

/**
 * Class Render
 * @package Framework\Base\Output
 */
abstract class Render implements RenderInterface
{
    /**
     * @param ResponseInterface $response
     * @return mixed
     */
    abstract public function render(ResponseInterface $response);
}
