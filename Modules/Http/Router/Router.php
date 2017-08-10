<?php

namespace Framework\Http\Router;

use Framework\Application\RestApi\NotFoundException;
use \Framework\Base\Module\ModuleInterface;

class Router implements ModuleInterface
{
    private $registry = [
        '/test' => '\Framework\User\Api\Actions\Single'
    ];

    public function bootstrap()
    {
    }

    /**
     * @param $uri
     * @return \Framework\Http\Request\ApiMethodInterface
     */
    public function parse($uri)
    {
        if (!isset($this->registry[$uri])) {
            throw new NotFoundException("Route for URI " . $uri ." is not registered");
        }

        return new $this->registry[$uri];
    }
}