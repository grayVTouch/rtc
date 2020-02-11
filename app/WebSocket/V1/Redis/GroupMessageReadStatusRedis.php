<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 15:44
 */

namespace App\WebSocket\V1\Redis;


class GroupMessageReadStatusRedis extends Redis
{
    // 设置消息读取状态
    public static function groupMessageReadStatusByIdentifierAndUserIdAndGroupMessageIdAndValue(string $identifier , int $user_id , int $group_message_id , $val = null)
    {
        $name = sprintf(self::$groupMessageReadStatus , $identifier , $user_id , $group_message_id);
        if (is_null($val)) {
            return self::string($name);
        }
        return self::string($name , $val);
    }

    public static function delByIdentifierAndUserIdAndGroupMessageId(string $identifier , int $user_id , int $group_message_id)
    {
        $name = sprintf(self::$groupMessageReadStatus , $identifier , $user_id , $group_message_id);
        return self::del($name);
    }
}