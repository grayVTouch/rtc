<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 15:42
 */

namespace App\Cache;


use App\Model\MessageReadStatusModel;
use App\Redis\MessageReadStatusRedis;
use function core\array_unit;

class MessageReadStatusCache extends Cache
{
    public static function insertGetId(string $identifier , int $user_id , int $message_id , string $chat_id , $is_read = 1)
    {
        $data = compact('identifier' , 'user_id' , 'message_id' , 'chat_id' , 'is_read');
        $id = MessageReadStatusModel::insertGetId(array_unit($data , [
            'identifier' ,
            'user_id' ,
            'message_id' ,
            'chat_id' ,
            'is_read'
        ]));
        $data['id'] = $id;
        MessageReadStatusRedis::messageReadStatus($identifier , $user_id , $message_id , (int) $is_read);
    }

    public static function isRead(string $identifier , int $user_id , int $message_id)
    {
        $is_read = MessageReadStatusRedis::messageReadStatus($identifier , $user_id , $message_id);
        if ($is_read === false) {
            $res= MessageReadStatusModel::findByUserIdAndMessageId($user_id , $message_id);
            if (empty($res)) {
                $is_read = 0;
            } else {
                $is_read = $res->is_read;
            }
            MessageReadStatusRedis::messageReadStatus($identifier , $user_id , $message_id , $is_read);
        }
        return (int) $is_read;
    }
}