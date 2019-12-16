<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 15:42
 */

namespace App\Data;


use App\Cache\MessageReadStatusCache;

class MessageReadStatusData extends Data
{
    public static function isRead(string $identifier , int $user_id , int $message_id)
    {
        return MessageReadStatusCache::isRead($identifier , $user_id , $message_id);
    }

    public static function insertGetId(string $identifier , int $user_id , int $message_id , string $chat_id , $is_read = 1)
    {
        return MessageReadStatusCache::insertGetId($identifier , $user_id , $message_id , $chat_id , $is_read);
    }
}