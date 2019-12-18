<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 11:24
 */

namespace App\Redis;

class UserJoinFriendOptionRedis extends Redis
{
    public static function userJoinFriendOptionByIdentifierAndUserIdAndJoinFriendMethodIdAndValue(string $identifier , int $user_id , int $join_friend_method_id , string $value = null)
    {
        $name = sprintf(self::$userJoinFriendOption , $identifier , $user_id , $join_friend_method_id);
        if (empty($value)) {
            $res = self::string($name);
            return json_decode($res);
        }
        return self::string($name , json_encode($value));
    }

    public static function delByIdentifierAndUserId(string $identifier , int $user_id , int $join_friend_method_id)
    {
        $name = sprintf(self::$userJoinFriendOption , $identifier , $user_id , $join_friend_method_id);
        return self::del($name);
    }
}