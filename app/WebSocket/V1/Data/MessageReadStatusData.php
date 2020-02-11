<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 15:42
 */

namespace App\WebSocket\V1\Data;


use App\WebSocket\V1\Cache\MessageReadStatusCache;
use App\WebSocket\V1\Model\MessageReadStatusModel;

class MessageReadStatusData extends Data
{
    public static function isReadByIdentifierAndUserIdAndMessageId(string $identifier , int $user_id , int $message_id)
    {
        return MessageReadStatusCache::isReadByIdentifierAndUserIdAndMessageId($identifier , $user_id , $message_id);
    }

    public static function delByIdentifierAndUserIdAndMessageId(string $identifier , int $user_id , int $message_id)
    {
        MessageReadStatusModel::delByUserIdAndMessageId($user_id , $message_id);
        MessageReadStatusCache::delByIdentifierAndUserIdAndMessageId($identifier , $user_id , $message_id);
    }

    public static function insertGetId(string $identifier , int $user_id , string $chat_id , int $message_id , int $is_read)
    {
        $id = MessageReadStatusModel::u_insertGetId($identifier , $user_id , $chat_id , $message_id , $is_read);
        MessageReadStatusCache::delByIdentifierAndUserIdAndMessageId($identifier , $user_id , $message_id);
        return $id;
    }
}