<?php

namespace Framework\User\Api\Controller;

use Framework\Base\Application\BaseController;
use Framework\User\Api\Model\User as UserModel;

/**
 * Class User
 * @package Framework\User\Api\Controller
 */
class User extends BaseController
{
    /**
     * @return array
     */
    public function getRegisteredRequestRoutes()
    {
        return [
            'get' => 'get',
            'post' => 'create',
        ];
    }

    /**
     * @return string
     */
    protected function get()
    {
        $user = $this->getRepository(UserModel::class)
            ->loadOne('598ddf36962d7456c80bfbb5');

        return $user->getId();
    }

    /**
     * @return bool|null
     */
    protected function create()
    {
        $user = new UserModel();
        $user->save();
        return $user->getId();
    }
}
