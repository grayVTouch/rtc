<?php

namespace App\Cache;

use App\Model\UserJoinFriendOptionModel;
use App\Redis\UserJoinFriendOptionRedis;

/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 11:23
 */

class UserJoinFriendMethodOptionCache
{
    public static function findByUserId(string $identifier , int $user_id)
    {
        $res = UserJoinFriendOptionRedis::userJoinFriendOption($identifier , $user_id);
        if (!empty($res)) {
            return json_decode($res);
        }
        $res = UserJoinFriendOptionModel::getByUserId($user_id);
        UserJoinFriendOptionRedis::userJoinFriendOption($identifier , $user_id , json_encode($res));
        return $res;
    }

    public static function updateByUserIdAndJoinFriendMethodId(string $identifier , $user_id , int $join_friend_method_id , array $data = [])
    {
        UserJoinFriendOptionModel::updateByUserIdAndJoinFriendMethodId($user_id , $join_friend_method_id , $data);
        UserJoinFriendOptionRedis::delUserJoinFriendOption($identifier , $user_id);
    }
}