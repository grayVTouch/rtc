<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 10:50
 */
namespace App\WebSocket\V1\Data;


use App\WebSocket\V1\Cache\UserCache;
use App\WebSocket\V1\Model\UserJoinFriendOptionModel;
use App\WebSocket\V1\Model\UserModel;

class UserData
{
    public static function findByIdentifierAndId(string $identifier , int $id)
    {
        $res = UserCache::findByIdentifierAndId($identifier , $id);
        if (empty($res)) {
            return ;
        }
        $res->user_option = UserOptionData::findByIdentifierAndUserId($identifier , $id);
        $res->user_join_friend_option = UserJoinFriendOptionModel::getByUserId($id);
        return $res;
    }

    public static function updateByIdentifierAndIdAndData(string $identifier , int $id , array $data = [])
    {
        UserModel::updateById($id , $data);
        UserCache::delByIdentifierAndId($identifier , $id);
    }

    public static function delByIdentifierAndId(string $identifier , $id)
    {
        UserModel::delById($id);
        UserCache::delByIdentifierAndId($identifier , $id);
    }
}