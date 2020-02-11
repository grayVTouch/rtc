<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/16
 * Time: 14:09
 */

namespace App\WebSocket\V1\Cache;


use App\WebSocket\V1\Model\GroupMemberModel;
use App\WebSocket\V1\Redis\GroupMemberRedis;

class GroupMemberCache extends Cache
{
    public static function findByIdentifierAndGroupIdAndUserId(string $identifier , int $group_id , int $user_id)
    {
        $cache = GroupMemberRedis::memberByIdentifierAndGroupIdAndUserIdAndValue($identifier , $group_id , $user_id);
        if (!empty($cache)) {
            return $cache;
        }
        $cache = GroupMemberModel::findByUserIdAndGroupId($user_id , $group_id);
        if (empty($cache)) {
            return ;
        }
        GroupMemberRedis::memberByIdentifierAndGroupIdAndUserIdAndValue($identifier , $group_id , $user_id , $cache);
        return $cache;
    }

    public static function delByIdentifierAndGroupIdAndUserId(string $identifier , int $group_id , int $user_id)
    {
        return GroupMemberRedis::delByIdentifierAndGroupIdAndUserId($identifier , $group_id , $user_id);
    }
}