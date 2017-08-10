<?php

namespace Modules\User\Api\Actions;

use Modules\Base\Handler\ApiMethod;
use Modules\Http\Request\ApiMethodInterface;

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