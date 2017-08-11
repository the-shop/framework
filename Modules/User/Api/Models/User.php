<?php

namespace Framework\User\Api\Models;

use Framework\Base\Model\Bruno;

class User extends Bruno
{
    protected $database = 'framework';

    protected $collection = 'users';
}