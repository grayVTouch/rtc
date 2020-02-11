<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 15:05
 */

namespace App\WebSocket\V1\Data;


use App\WebSocket\V1\Cache\JoinFriendMethodCache;

class JoinFriendMethodData
{
    public static function findByIdentifierAndId(string $identifier , $id)
    {
        $res = JoinFriendMethodCache::findByIdentifierAndId($identifier , $id);
        return $res;
    }
}