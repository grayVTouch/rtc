<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 15:44
 */

namespace App\Redis;


class MessageReadStatusRedis extends Redis
{
    // 设置消息读取状态
    public static function messageReadStatus(string $identifier , int $user_id , int $message_id , $val = null)
    {
        $name = sprintf(self::$messageReadStatus , $identifier , $user_id , $message_id);
        if (is_null($val)) {
            return self::string($name);
        }
        return self::string($name , $val);
    }

    public static function delMessageReadStatus(string $identifier , int $user_id , int $message_id)
    {
        $name = sprintf(self::$messageReadStatus , $identifier , $user_id , $message_id);
        return self::del($name);
    }

    // 检查 key 是否存在
    public static function existMessageReadStatus(string $identifier , int $user_id , int $message_id)
    {

    }
}