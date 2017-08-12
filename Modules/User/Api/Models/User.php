<?php

namespace Framework\User\Api\Models;

use Framework\Base\Model\Bruno;

/**
 * Class User
 * @package Framework\User\Api\Models
 */
class User extends Bruno
{
    protected $database = 'framework';

    protected $collection = 'users';
}