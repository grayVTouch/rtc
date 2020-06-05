<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 15:42
 */

namespace App\Http\ApiV1\Cache;

use App\Http\ApiV1\Model\GroupMessageReadStatusModel;
use App\Http\ApiV1\Redis\GroupMessageReadStatusRedis;
use function core\array_unit;

class GroupMessageReadStatusCache extends Cache
{
    public static function isReadByIdentifierAndUserIdAndGroupMessageId(string $identifier , int $user_id , int $group_message_id)
    {
        $is_read = GroupMessageReadStatusRedis::groupMessageReadStatusByIdentifierAndUserIdAndGroupMessageIdAndValue($identifier , $user_id , $group_message_id);
        if ($is_read === false) {
            // key 不存在
            $res= GroupMessageReadStatusModel::findByUserIdAndGroupMessageId($user_id , $group_message_id);
            if (empty($res)) {
                $is_read = 0;
            } else {
                $is_read = $res->is_read;
            }
            GroupMessageReadStatusRedis::groupMessageReadStatusByIdentifierAndUserIdAndGroupMessageIdAndValue($identifier , $user_id , $group_message_id , $is_read);
        }
        return $is_read;
    }

    public static function delByIdentifierAndUserIdAndGroupMessageId(string $identifier , int $user_id , int $group_message_id)
    {
        return GroupMessageReadStatusRedis::delByIdentifierAndUserIdAndGroupMessageId($identifier , $user_id , $group_message_id);
    }
}