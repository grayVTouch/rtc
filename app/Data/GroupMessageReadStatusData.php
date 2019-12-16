<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 15:42
 */

namespace App\Data;


use App\Cache\GroupMessageReadStatusCache;

class GroupMessageReadStatusData extends Data
{
    public static function isRead(string $identifier , int $user_id , int $group_message_id)
    {
        return GroupMessageReadStatusCache::isRead($identifier , $user_id , $group_message_id);
    }

    public static function insertGetId(string $identifier , int $user_id , int $group_message_id , int $group_id , $is_read = 1)
    {
        return GroupMessageReadStatusCache::insertGetId($identifier , $user_id , $group_message_id , $group_id , $is_read);
    }
}