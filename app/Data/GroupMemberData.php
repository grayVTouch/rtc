<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/16
 * Time: 18:49
 */

namespace App\Data;


use App\Cache\GroupMemberCache;
use App\Model\GroupMemberModel;

class GroupMemberData extends Data
{
    public static function findByIdentifierAndGroupIdAndUserId(string $identifier , int $group_id , int $user_id)
    {
        $member = GroupMemberCache::findByIdentifierAndGroupIdAndUserId($identifier , $group_id , $user_id);
        if (empty($member)) {
            return ;
        }
        $member->user = UserData::findByIdentifierAndId($identifier , $member->user_id);
        $member->group = GroupData::findByIdentifierAndId($identifier , $member->group_id);
        return $member;
    }

    public static function updateByIdentifierAndGroupIdAndUserIdAndData(string $identifier , int $group_id , int $user_id , array $data = [])
    {
        GroupMemberModel::updateByUserIdAndGroupId($user_id , $group_id , $data);
        GroupMemberCache::delByIdentifierAndGroupIdAndUserId($identifier , $group_id , $user_id);
    }

    public static function delByIdentifierAndGroupIdAndUserId(string $identifier , int $group_id , int $user_id)
    {
        GroupMemberModel::delByUserIdAndGroupId($user_id , $group_id);
        GroupMemberCache::delByIdentifierAndGroupIdAndUserId($identifier , $group_id , $user_id);
    }


}