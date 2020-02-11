<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/16
 * Time: 14:19
 */

namespace App\WebSocket\V1\Redis;


class GroupMemberRedis extends Redis
{
    public static function memberByIdentifierAndGroupIdAndUserIdAndValue(string $identifier , int $group_id , int $user_id , $value = null)
    {
        $name = sprintf(self::$groupMember , $identifier , $group_id , $user_id);
        if (is_null($value)) {
            $res = self::string($name);
            return json_decode($res);
        }
        return self::string($name , json_encode($value));
    }

    public static function delByIdentifierAndGroupIdAndUserId(string $identifier , int $group_id , int $user_id)
    {
        $name = sprintf(self::$groupMember , $identifier , $group_id , $user_id);
        return self::del($name);
    }
}