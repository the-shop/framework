<?php

namespace Framework\User\Api\Actions;

use Framework\Base\Handler\ApiMethod;
use Framework\Http\Request\ApiMethodInterface;

class Single extends ApiMethod implements ApiMethodInterface
{
    public function getRegisteredRequestRoutes()
    {
        return [
            'get' => 'get',
            'post' => 'create',
            'put' => 'update',
            'delete' => 'delete',
        ];
    }

    protected function get()
    {
        return 'output 1';
    }

    protected function create()
    {
        return 'output 2';
    }

    protected function update()
    {
        return 'output 3';
    }

    protected function delete()
    {
        return 'output 4';
    }
}