<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 11:24
 */

namespace App\Http\ApiV1\Redis;

use Engine\Facade\Redis as RedisFacade;

class JoinFriendMethodRedis extends Redis
{
    public static function joinFriendMethod(string $identifier , int $join_friend_method_id , string $value = null)
    {
        $name = sprintf(self::$joinFriendMethod , $identifier , $join_friend_method_id);
        if (empty($value)) {
            return RedisFacade::string($name);
        }
        return RedisFacade::string($name , $value , config('app.cache_duration'));
    }

    public static function delJoinFriendMethod(string $identifier , int $join_friend_method_id)
    {
        $name = sprintf(self::$joinFriendMethod , $identifier , $join_friend_method_id);
        return RedisFacade::del($name);
    }
}