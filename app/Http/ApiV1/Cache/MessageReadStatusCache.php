<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 15:42
 */

namespace App\Http\ApiV1\Cache;


use App\Http\ApiV1\Model\MessageReadStatusModel;
use App\Http\ApiV1\Redis\MessageReadStatusRedis;
use function core\array_unit;

class MessageReadStatusCache extends Cache
{
    public static function isReadByIdentifierAndUserIdAndMessageId(string $identifier , int $user_id , int $message_id)
    {
        $is_read = MessageReadStatusRedis::messageReadStatusByIdentifierAndUserIdAndMessageIdAndValue($identifier , $user_id , $message_id);
        if ($is_read === false) {
            $res= MessageReadStatusModel::findByUserIdAndMessageId($user_id , $message_id);
            if (empty($res)) {
                $is_read = 0;
            } else {
                $is_read = $res->is_read;
            }
            MessageReadStatusRedis::messageReadStatusByIdentifierAndUserIdAndMessageIdAndValue($identifier , $user_id , $message_id , $is_read);
        }
        return (int) $is_read;
    }

    public static function delByIdentifierAndUserIdAndMessageId(string $identifier , int $user_id , int $message_id)
    {
        return MessageReadStatusRedis::delByIdentifierAndUserIdAndMessageId($identifier , $user_id , $message_id);
    }
}