<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 15:44
 */

namespace App\WebSocket\V1\Redis;


class MessageReadStatusRedis extends Redis
{
    // 设置消息读取状态
    public static function messageReadStatusByIdentifierAndUserIdAndMessageIdAndValue(string $identifier , int $user_id , int $message_id , $val = null)
    {
        $name = sprintf(self::$messageReadStatus , $identifier , $user_id , $message_id);
        if (is_null($val)) {
            return self::string($name);
        }
        return self::string($name , $val);
    }

    public static function delByIdentifierAndUserIdAndMessageId(string $identifier , int $user_id , int $message_id)
    {
        $name = sprintf(self::$messageReadStatus , $identifier , $user_id , $message_id);
        return self::del($name);
    }

}