<?php

namespace Framework\User\Api;

use Framework\Base\Module\Module;

class Api extends Module
{
    private $config = [
        'routes' => [
            '/test' => '\Framework\User\Api\Actions\Single'
        ]
    ];

    public function bootstrap()
    {
        $this->getApplication()
            ->getRouter()
            ->registerRoutes($this->config['routes']);
    }
}
