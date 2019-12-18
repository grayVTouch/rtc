<?php

namespace App\Cache;

use App\Model\UserOptionModel;
use App\Redis\UserOptionRedis;

/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 11:23
 */

class UserOptionCache
{
    public static function findByIdentifierAndUserId(string $identifier , int $user_id)
    {
        $cache = UserOptionRedis::userOptionByIdentifierAndUserIdAndValue($identifier , $user_id);
        if (!empty($cache)) {
            return $cache;
        }
        $cache = UserOptionModel::findByUserId($user_id);
        UserOptionRedis::userOptionByIdentifierAndUserIdAndValue($identifier , $user_id , $cache);
        return $cache;
    }

    public static function delByIdentifierAndUserId(string $identifier , $user_id)
    {
        return UserOptionRedis::delByIdentifierAndUserId($identifier , $user_id);
    }
}