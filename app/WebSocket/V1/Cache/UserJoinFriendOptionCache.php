<?php

namespace App\WebSocket\V1\Cache;

use App\WebSocket\V1\Model\UserJoinFriendOptionModel;
use App\WebSocket\V1\Redis\UserJoinFriendOptionRedis;

/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 11:23
 */

class UserJoinFriendOptionCache extends Cache
{
    public static function findByIdentifierAndUserIdAndJoinFriendMethodId(string $identifier , int $user_id , int $join_friend_method_id)
    {
        $cache = UserJoinFriendOptionRedis::userJoinFriendOptionByIdentifierAndUserIdAndJoinFriendMethodIdAndValue($identifier , $user_id , $join_friend_method_id);
        if (!empty($cache)) {
            return $cache;
        }
        $cache = UserJoinFriendOptionModel::findByUserIdAndJoinFriendMethodId($user_id , $join_friend_method_id);
        UserJoinFriendOptionRedis::userJoinFriendOptionByIdentifierAndUserIdAndJoinFriendMethodIdAndValue($identifier , $user_id , $cache);
        return $cache;
    }

    public static function delByIdentifierAndUserId(string $identifier , int $user_id , int $join_friend_method_id)
    {
        return UserJoinFriendOptionRedis::delByIdentifierAndUserId($identifier,  $user_id , $join_friend_method_id);
    }
}