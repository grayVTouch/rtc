<?php

namespace App\Http\ApiV1\Data;

use App\Http\ApiV1\Cache\UserOptionCache;
use App\Http\ApiV1\Model\UserOptionModel;

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

    public static function updateByIdentifierAndUserIdAndData(string $identifier , int $user_id , array $data = [])
    {
        UserOptionModel::updateByUserId($user_id , $data);
        UserOptionCache::delByIdentifierAndUserId($identifier , $user_id);
    }

    public static function delByIdentifierAndUserId(string $identifier , int $user_id)
    {
        UserOptionModel::delByUserId($user_id);
        UserOptionCache::delByIdentifierAndUserId($identifier , $user_id);
    }
}