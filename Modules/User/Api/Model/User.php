<?php

namespace Framework\User\Api\Model;

use Framework\Base\Model\Bruno;

/**
 * Class User
 * @package Framework\User\Api\Model
 */
class User extends Bruno
{
    protected $database = 'framework';

    protected $collection = 'users';
}