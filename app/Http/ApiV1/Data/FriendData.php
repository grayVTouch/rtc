<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/16
 * Time: 18:49
 */

namespace App\Http\ApiV1\Data;


use App\Http\ApiV1\Cache\FriendCache;
use App\Http\ApiV1\Model\FriendModel;

class FriendData extends Data
{
    public static function findByIdentifierAndUserIdAndFriendId(string $identifier , int $user_id , int $friend_id)
    {
        $friend = FriendCache::findByIdentifierAndUserIdAndFriendId($identifier , $user_id , $friend_id);
        if (empty($friend)) {
            return ;
        }
        $friend->user = UserData::findByIdentifierAndId($identifier , $friend->user_id);
        $friend->friend = GroupData::findByIdentifierAndId($identifier , $friend->friend_id);
        return $friend;
    }

    public static function updateByIdentifierAndUserIdAndFriendIdAndData(string $identifier , int $user_id , int $friend_id , array $data = [])
    {
        FriendModel::updateByUserIdAndFriendId($user_id , $friend_id , $data);
        FriendCache::delByIdentifierAndUserIdAndFriendId($identifier , $user_id , $friend_id);
    }

    public static function delByIdentifierAndUserIdAndFriendId(string $identifier , int $user_id , int $friend_id)
    {
        FriendModel::delByUserIdAndFriendId($user_id , $friend_id);
        FriendCache::delByIdentifierAndUserIdAndFriendId($identifier , $user_id , $friend_id);
    }

}