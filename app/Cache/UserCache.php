<?php

use App\Model\UserModel;
use App\Redis\UserRedis;

/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 10:55
 */

class UserCache
{
    public static function findById(string $identifier , int $user_id)
    {
        $user = UserRedis::user($identifier , $user_id);
        if (!empty($user)) {
            return json_decode($user);
        }
        $user = UserModel::findById($user_id);
        UserRedis::user($identifier , $user_id , json_encode($user));
        return $user;
    }

    public static function updateById(string $identifier , $user_id , array $data = [])
    {
        UserModel::updateById($user_id , $data);
        UserRedis::delUser($identifier , $user_id);
    }

    public static function updateByIds(string $identifier , array $user_id = [] , array $data = [])
    {
        foreach ($user_id as $v)
        {
            self::updateById($identifier , $v , $data);
        }
    }
}