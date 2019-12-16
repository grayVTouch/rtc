<?php

namespace App\Cache;

use App\Model\JoinFriendMethodModel;
use App\Redis\JoinFriendMethodRedis;

/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 14:41
 */

class JoinFriendMethodCache
{
    public static function findByIdentifierAndId(string $identifier , int $id)
    {
        $res = JoinFriendMethodRedis::joinFriendMethod($identifier , $id);
        if (!empty($res)) {
            return json_decode($res);
        }
        $res = JoinFriendMethodModel::findById($id);
        JoinFriendMethodRedis::joinFriendMethod($identifier , $id , json_encode($res));
        return $res;
    }

    public static function updateById(string $identifier , int $id , array $data = [])
    {
        JoinFriendMethodModel::updateById($id , $data);
        JoinFriendMethodRedis::delJoinFriendMethod($identifier , $id);
    }
}