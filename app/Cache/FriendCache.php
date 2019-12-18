<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/16
 * Time: 14:18
 */

namespace App\Cache;


use App\Model\FriendModel;
use App\Redis\FriendRedis;

class FriendCache extends Cache
{
    public static function findByIdentifierAndUserIdAndFriendId(string $identifier , int $user_id , int $friend_id)
    {
        $cache = FriendRedis::friendByIdentifierAndUserIdAndFriendIdAndValue($identifier , $user_id , $friend_id);
        if (!empty($cache)) {
            return $cache;
        }
        $cache = FriendModel::findByUserIdAndFriendIdWithV1($user_id , $friend_id);
        if (empty($cache)) {
            return ;
        }
        FriendRedis::friendByIdentifierAndUserIdAndFriendIdAndValue($identifier , $user_id , $friend_id , $cache);
        return $cache;
    }

    public static function delByIdentifierAndUserIdAndFriendId(string $identifier , int $user_id , int $friend_id)
    {
        return FriendRedis::delByIdentifierAndUserIdAndFriendId($identifier , $user_id , $friend_id);
    }
}