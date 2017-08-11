<?php

namespace Framework\User\Api\Actions;

use Framework\Base\Handler\ApiMethod;
use Framework\User\Api\Models\User;

class Single extends ApiMethod
{
    public function getRegisteredRequestRoutes()
    {
        return [
            'get' => 'get',
            'post' => 'create',
        ];
    }

    protected function create()
    {
        $user = new User();
        $user->save();
        return $user->getId();
    }

    protected function get()
    {
        $user = $this->getRepository(User::class)
            ->loadOne('598ddf36962d7456c80bfbb5');
        return $user;
    }
}
