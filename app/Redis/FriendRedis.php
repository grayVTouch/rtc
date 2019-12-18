<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/16
 * Time: 14:19
 */

namespace App\Redis;


class FriendRedis extends Redis
{
    public static function friendByIdentifierAndUserIdAndFriendIdAndValue(string $identifier , int $user_id , int $friend_id , $value = null)
    {
        $name = sprintf(self::$friend , $identifier , $user_id , $friend_id);
        if (is_null($value)) {
            $res = self::string($name);
            return json_decode($res);
        }
        return self::string($name , json_encode($value));
    }

    public static function delByIdentifierAndUserIdAndFriendId(string $identifier , int $user_id , int $friend_id)
    {
        $name = sprintf(self::$friend , $identifier , $user_id , $friend_id);
        return self::del($name);
    }
}