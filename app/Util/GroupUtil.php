<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/29
 * Time: 10:22
 */

namespace App\Util;


use App\Model\ApplicationModel;
use App\Model\GroupMemberModel;
use App\Model\GroupMessageModel;
use App\Model\GroupModel;
use App\Model\SessionModel;
use App\Model\UserModel;

class GroupUtil extends Util
{
    /**
     * @param \App\Model\GroupModel|\StdClass $group
     * @return null
     */
    public static function handle($group , int $user_id = 0)
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

        if (!empty($user_id)) {
            // 我再本群的昵称
            $myself = GroupMemberModel::findByUserIdAndGroupId($user_id , $group->id);
            if (!empty($myself)) {
                $user = UserModel::findById($user_id);
                if (!empty($user)) {
                    $group->my_alias = empty($myself->alias) ?
                        $user->nickname :
                        $myself->alias;
                }
            }
            // 是否置顶
            $group->top = empty($myself) ? 0 : $myself->top;
            $group->can_notice = empty($myself) ? 1 : $myself->can_notice;
            $group->background = empty($myself) ? '' : $myself->background;
        }

    }

    // 删除群（执行该方法请始终使用事务的方式执行）
    public static function delete(int $group_id)
    {
        $group_message_ids = GroupMessageModel::getIdByGroupId($group_id);
        foreach ($group_message_ids as $v)
        {
            // 删除消息
            GroupMessageUtil::delete($v);
        }
        // 删除会话
        SessionModel::delByTypeAndTargetId('group' , $group_id);
        // 删除群成员
        GroupMemberModel::delByGroupId($group_id);
        // 删除群
        GroupModel::delById($group_id);
        // 验证消息
        ApplicationModel::delGroupApplication($group_id);
    }
}