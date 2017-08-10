<?php

namespace Framework\User\Api\Actions;

use Framework\Base\Handler\ApiMethod;
use Framework\Http\Request\ApiMethodInterface;

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
        // TODO:
//        $user = new User();
//        $user->save();
        return 'TODO: implement me';
    }
}
