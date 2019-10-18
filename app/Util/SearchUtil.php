<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/18
 * Time: 9:54
 */

namespace App\Util;


use App\Model\FriendModel;
use App\Model\GroupMemberModel;
use App\Model\GroupMessageModel;
use App\Model\GroupModel;
use App\Model\MessageModel;
use App\Model\SessionModel;
use App\Model\UserModel;

class SearchUtil extends Util
{
    // 本地搜索-好友
    public static function searchUserByUserIdAndValueAndLimitForLocal(int $user_id , $value , int $limit = 0): array
    {
        $user = UserModel::findById($user_id);
        $qualified_users = [];
        if (mb_strpos($user->nickname , $value) !== false) {
            $qualified_users[] = $user;
        }
        // 搜索好友
        $friends = FriendModel::searchByUserIdAndValueAndLimit($user->id , $value ,empty($qualified_users) ? $limit : max(0 , $limit - 1));
        foreach ($friends as $v)
        {
            UserUtil::handle($v->friend , $user->id);
            $qualified_users[] = $v->friend;
        }
        return $qualified_users;
    }

    // 本地搜索-群组
    public static function searchGroupByUserIdAndValueAndLimitForLocal(int $user_id , $value , $limit = 0): array
    {
        $qualified_groups = [];
        $group_ids = GroupMemberModel::getGroupIdByUserId($user_id);
        foreach ($group_ids as $v)
        {
            $member = GroupMemberModel::searchByGroupIdAndValueOnlyFirst($v , $value);
            if (empty($member)) {
                continue ;
            }
            if (!empty($limit) && count($qualified_groups) >= $limit) {
                return $qualified_groups;
            }
            $group = $member->group;
            $group->member = $member->user;

            GroupUtil::handle($group->group);
            UserUtil::handle($group->member , $user_id);
            $qualified_groups[] = $group;
        }
        return $qualified_groups;
    }

    // 本地搜索-私聊会话
    public static function searchPrivateSessionByUserIdAndValueAndLimitForLocal(int $user_id , $value , int $limit = 0): array
    {
        $sessions = SessionModel::getByUserIdAndType($user_id , 'private');
        $qualified_private_for_history = [];
        foreach ($sessions as $v)
        {
            $relation_message_count= MessageModel::countByChatIdAndValue($v->target_id , $value);
            $other_id = ChatUtil::otherId($v->target_id , $user_id);
            $other = UserModel::findById($other_id);
            if ($relation_message_count < 1) {
                // 没有相关聊天记录，跳过
                continue ;
            }
            if (!empty($limit) && count($qualified_private_for_history) >= 3) {
                break;
            }
            $other->relation_message_count = $relation_message_count;
            // 私聊
            $other->type = 'private';
            UserUtil::handle($other , $user_id);
            $qualified_private_for_history[] = $other;
        }
        return $qualified_private_for_history;
    }

    // 本地搜索-群聊会话
    public static function searchGroupSessionByUserIdAndValueAndLimitForLocal(int $user_id , $value , int $limit = 0): array
    {
        $sessions = SessionModel::getByUserIdAndType($user_id , 'group');
        $qualified_group_for_history = [];
        foreach ($sessions as $v)
        {
            $relation_message_count = GroupMessageModel::countByGroupIdAndValue($v->target_id , $value);
            if ($relation_message_count < 1) {
                // 没有相关聊天记录
                continue ;
            }
            if (!empty($limit) && count($qualified_group_for_history) >= $limit) {
                break;
            }
            $group = GroupModel::findById($v->target_id);
            $group->relation_message_count = $relation_message_count;
            // 群聊
            $group->type = 'group';
            GroupUtil::handle($group);
            $qualified_group_for_history[] = $group;
        }
        return $qualified_group_for_history;
    }

    // 本地搜索-单个会话私聊记录
    public static function searchPrivateHistoryByUserIdChatIdAndValueAndLimitIdAndLimitForLocal(int $user_id , string $chat_id , $value , int $limit_id = 0 , int $limit = 30)
    {
        $res = MessageModel::searchByChatIdAndValueAndLimitIdAndLimit($chat_id , $value , $limit_id , $limit);
        foreach ($res as $v)
        {
            UserUtil::handle($v->user , $user_id);
        }
        return $res;
    }

    // 本地搜索-群聊记录
    public static function searchGroupHistoryByUserIdAndGroupIdAndValueAndLimitIdAndLimitForLocal(int $user_id , int $group_id , $value , int $limit_id = 0 , int $limit = 30)
    {
        $res = GroupMessageModel::searchByGroupIdAndValueAndLimitIdAndLimit($group_id , $value , $limit_id , $limit);
        foreach ($res as $v)
        {
            UserUtil::handle($v->user , $user_id);
        }
        return $res;
    }
}