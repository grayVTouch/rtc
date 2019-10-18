<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/29
 * Time: 10:22
 */

namespace App\Util;


use App\Model\GroupMemberModel;
use App\Model\GroupMessageModel;
use App\Model\GroupModel;
use App\Model\SessionModel;

class GroupUtil extends Util
{
    /**
     * @param \App\Model\GroupModel|\StdClass $group
     * @return null
     */
    public static function handle($group)
    {
        if (empty($group)) {
            return ;
        }
        $member = GroupMemberModel::getByGroupId($group->id , 9);
        $member_avatar = [];
        foreach ($member as $v)
        {
            $member_avatar[] = [
                'avatar'    => !empty($v->user) ? $v->user->avatar : '' ,
                'nickname'  => !empty($v->user) ? $v->user->nickname : '' ,
            ];
        }
        $group->member_avatar = $member_avatar;
        // 成员数量
        $group->member_count = GroupMemberModel::countByGroupId($group->id);
        // 群聊人数限制
        $group->group_member_limit = config('app.group_member_limit');
    }

    // 删除群（执行该方法请始终使用事务的方式执行）
    public static function delete(int $group_id)
    {
        $group_message_ids = GroupMessageModel::getIdByGroupId($group_id);
        foreach ($group_message_ids as $v1)
        {
            // 删除消息
            GroupMessageUtil::delete($v1);
        }
        // 删除置顶群（会话）
        SessionModel::delByTypeAndTargetId('group' , $group_id);
        // 删除群成员
        GroupMemberModel::delByGroupId($group_id);
        // 删除群
        GroupModel::delById($group_id);
    }
}