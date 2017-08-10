<?php

namespace Modules\Http\Router;

use Modules\Application\RestApi\NotFoundException;
use \Modules\Base\Module\ModuleInterface;

class Router implements ModuleInterface
{
    private $registry = [
        '/test' => '\Modules\User\Api\Actions\Single'
    ];

    public function bootstrap()
    {
    }

    /**
     * @param $uri
     * @return \Modules\Http\Request\ApiMethodInterface
     */
    public function parse($uri)
    {
        if (!isset($this->registry[$uri])) {
            throw new NotFoundException("Route for URI " . $uri ." is not registered");
        }

        return new $this->registry[$uri];
    }
}