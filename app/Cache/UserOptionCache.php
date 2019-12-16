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
        $res = UserOptionRedis::userOption($identifier , $user_id);
        if (!empty($res)) {
            return json_decode($res);
        }
        $res = UserOptionModel::findByUserId($user_id);
        UserOptionRedis::userOption($identifier , $user_id , json_encode($res));
        return $res;
    }

    public static function updateById(string $identifier , $user_id , array $data = [])
    {
        UserOptionModel::updateById($user_id , $data);
        UserOptionRedis::delUserOption($identifier , $user_id);
    }

    public static function updateByIds(string $identifier , array $user_id = [] , array $data = [])
    {
        foreach ($user_id as $v)
        {
            self::updateById($identifier , $v , $data);
        }
    }
}