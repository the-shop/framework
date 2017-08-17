<?php

namespace Framework\Base\Render;

use Framework\Base\Response\ResponseInterface;

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
