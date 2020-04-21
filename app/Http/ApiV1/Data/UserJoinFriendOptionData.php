<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/17
 * Time: 9:53
 */

namespace App\Http\ApiV1\Data;


use App\Http\ApiV1\Cache\UserJoinFriendOptionCache;
use App\Http\ApiV1\Model\UserJoinFriendOptionModel;

class UserJoinFriendOptionData extends Data
{
    public static function findByIdentifierAndUserIdAndJoinFriendMethodId(string $identifier , int $user_id , int $join_friend_method_id)
    {
        $res = UserJoinFriendOptionCache::findByIdentifierAndUserIdAndJoinFriendMethodId($identifier , $user_id , $join_friend_method_id);
        return $res;
    }

    public static function updateByIdentifierAndUserIdAndJoinFriendMethodIdAndData(string $identifier , int $user_id , int $join_friend_method_id , array $data = [])
    {
        UserJoinFriendOptionModel::updateByUserIdAndJoinFriendMethodId($user_id , $join_friend_method_id , $data);
        UserJoinFriendOptionCache::delByIdentifierAndUserId($identifier , $user_id , $join_friend_method_id);
    }

    public static function delByIdentifierAndUserIdAndJoinFriendMethodId(string $identifier , int $user_id , int $join_friend_method_id)
    {
        UserJoinFriendOptionModel::delByUserIdAndJoinFriendMethodId($user_id , $join_friend_method_id);
        UserJoinFriendOptionCache::delByIdentifierAndUserId($identifier , $user_id , $join_friend_method_id);
    }

    public static function getByUserId(int $user_id)
    {
        return UserJoinFriendOptionModel::getByUserId($user_id);
    }

}