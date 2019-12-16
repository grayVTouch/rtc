<?php

namespace App\Data;

use App\Cache\UserOptionCache;

/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 10:50
 */

class UserOptionData
{
    public static function findByIdentifierAndUserId(string $identifier , int $user_id)
    {
        $res = UserOptionCache::findByIdentifierAndUserId($identifier , $user_id);
        return $res;
    }
}