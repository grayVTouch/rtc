<?php

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
    public static function findByUserId(string $identifier , int $user_id)
    {
        $user = UserOptionRedis::user($identifier , $user_id);
        if (!empty($user)) {
            return json_decode($user);
        }
        $user = UserOptionModel::findByUserId($user_id);
        UserOptionRedis::user($identifier , $user_id , json_encode($user));
        return $user;
    }

    public static function updateById(string $identifier , $user_id , array $data = [])
    {
        UserOptionModel::updateById($user_id , $data);
        UserOptionRedis::delUser($identifier , $user_id);
    }

    public static function updateByIds(string $identifier , array $user_id = [] , array $data = [])
    {
        foreach ($user_id as $v)
        {
            self::updateById($identifier , $v , $data);
        }
    }
}