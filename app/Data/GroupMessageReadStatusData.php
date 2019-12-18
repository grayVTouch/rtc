<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 15:42
 */

namespace App\Data;


use App\Cache\GroupMessageReadStatusCache;
use App\Model\GroupMessageReadStatusModel;

class GroupMessageReadStatusData extends Data
{
    public static function isReadByIdentifierAndUserIdAndGroupMessageId(string $identifier , int $user_id , int $group_message_id)
    {
        return GroupMessageReadStatusCache::isReadByIdentifierAndUserIdAndGroupMessageId($identifier , $user_id , $group_message_id);
    }

    public static function insertGetId(string $identifier , int $user_id , int $group_message_id , int $group_id , $is_read = 1)
    {
        $id = GroupMessageReadStatusModel::u_insertGetId($identifier , $user_id , $group_id , $group_message_id ,  $is_read);
        GroupMessageReadStatusCache::delByIdentifierAndUserIdAndGroupMessageId($identifier , $user_id , $group_message_id);
        return $id;
    }
}