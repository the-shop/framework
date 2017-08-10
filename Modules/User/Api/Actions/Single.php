<?php

namespace Framework\User\Api\Actions;

use Framework\Base\Handler\ApiMethod;
use Framework\Http\Request\ApiMethodInterface;
use Framework\User\Api\Models\User;

class Single extends ApiMethod implements ApiMethodInterface
{
    public function getRegisteredRequestRoutes()
    {
        return [
            'post' => 'create',
        ];
    }

    protected function create()
    {
        $user = new User();
        $user->save();
        return $user->getId();
    }
}
