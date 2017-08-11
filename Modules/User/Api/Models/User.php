<?php

namespace Framework\User\Api\Models;

use Framework\Base\Model\Bruno;

class User extends Bruno
{
    protected $address = '192.168.33.10:27017';

    protected $database = 'framework';

    protected $collection = 'users';

}