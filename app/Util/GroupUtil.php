<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/29
 * Time: 10:22
 */

namespace App\Util;


use App\Data\GroupData;
use App\Data\GroupMemberData;
use App\Data\UserData;
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
        $members = GroupMemberModel::getByGroupIdAndLimit($group->id , 9);
        $member_avatar = [];
        foreach ($members as $v)
        {
            $user = UserData::findByIdentifierAndId($group->identifier , $v->user_id);
            $member_avatar[] = [
                'avatar'    => !empty($user) ? $user->avatar : '' ,
                'nickname'  => !empty($user) ? UserUtil::getNameFromNicknameAndUsername($user->nickname , $user->username) : '' ,
            ];
        }
        $group->member_avatar = $member_avatar;
        // 成员数量
        $group->member_count = GroupMemberModel::countByGroupId($group->id);
        // 群聊人数限制
        $group->group_member_limit = config('app.group_member_limit');

        if (!empty($user_id)) {
            // 我再本群的昵称
            $relation = GroupMemberData::findByIdentifierAndGroupIdAndUserId($group->identifier , $group->id , $user_id);
            if (!empty($relation)) {
                $user = UserData::findByIdentifierAndId($group->identifier , $user_id);
                if (!empty($user)) {
                    $group->my_alias = empty($relation->alias) ?
                        $user->nickname :
                        $relation->alias;
                }
            }
            // 是否置顶
            $group->top = empty($relation) ? 0 : $relation->top;
            $group->can_notice = empty($relation) ? 1 : $relation->can_notice;
            $group->background = empty($relation) ? '' : $relation->background;
        }
    }

    // 删除群（执行该方法请始终使用事务的方式执行）
    public static function delete(string $identifier , int $group_id)
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
        $members = GroupMemberModel::getByGroupId($group_id);
        foreach ($members as $v)
        {
            GroupMemberData::delByIdentifierAndGroupIdAndUserId($v->identifier , $v->group_id , $v->user_id);
        }
        // 删除群
        GroupData::delByIdentifierAndId($identifier , $group_id);
        // 验证消息
        ApplicationModel::delGroupApplication($group_id);
    }
}