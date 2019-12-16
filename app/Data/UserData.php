<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 10:50
 */
namespace App\Data;


use App\Cache\UserCache;

class UserData
{
    public static function findByIdentifierAndId(string $identifier , int $id)
    {
        $res = UserCache::findByIdentifierAndId($identifier , $id);
        if (empty($res)) {
            return ;
        }
        $res->user_option = UserOptionData::findByIdentifierAndUserId($identifier , $id);
        $res->user_join_friend_option = UserJoinFriendOptionData::getByIdentifierAndUserId($identifier , $id);
        return $res;
    }
}