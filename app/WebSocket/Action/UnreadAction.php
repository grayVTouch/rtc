<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/20
 * Time: 13:52
 */

namespace App\WebSocket\Action;


use App\Data\FriendData;
use App\Data\GroupMemberData;
use App\Model\ApplicationModel;
use App\Model\GroupMessageReadStatusModel;
use App\Model\MessageReadStatusModel;
use App\Model\PushModel;
use App\Model\SessionModel;
use App\Model\UserModel;
use App\Util\ChatUtil;
use App\WebSocket\Auth;

class UnreadAction extends Action
{

    public static function unread(Auth $auth , array $param)
    {
        $session = SessionModel::getByUserId($auth->user->id);
        $unread_count_by_private = 0;
        $unread_count_by_group = 0;
        foreach ($session as $v)
        {
            if ($v->type == 'private') {
                // 检查是否开启了免打扰
                $other_id = ChatUtil::otherId($v->target_id , $v->user_id);
                $relation = FriendData::findByIdentifierAndUserIdAndFriendId($v->identifier , $v->user_id , $other_id);
                $can_notice = empty($relation) ? 1 : $relation->can_notice;
                $unread_count_by_private += $can_notice == 1 ? MessageReadStatusModel::unreadCountByUserIdAndChatId($auth->user->id , $v->target_id) : 0;
//                $unread_count_by_private += MessageReadStatusModel::unreadCountByUserIdAndChatId($auth->user->id , $v->target_id);
//                $unread_count_by_private += $can_notice == 1 ? MessageReadStatusModel::unreadCountByUserIdAndChatId($auth->user->id , $v->target_id) : 0;

            }
            if ($v->type == 'group') {
//                $member = GroupMemberData::findByIdentifierAndGroupIdAndUserId($auth->identifier , $v->target_id , $auth->user->id);
//                $can_notice = empty($member) ? 1 : $member->can_notice;
//                $unread_count_by_group += $can_notice == 1 ? GroupMessageReadStatusModel::unreadCountByUserIdAndGroupId($auth->user->id , $v->target_id) : 0;
                $member = GroupMemberData::findByIdentifierAndGroupIdAndUserId($auth->identifier , $v->target_id , $v->user_id);
                $can_notice = empty($member) ? 1 : $member->can_notice;
                $unread_count_by_group += $can_notice == 1 ? GroupMessageReadStatusModel::unreadCountByUserIdAndGroupId($auth->user->id , $v->target_id) : 0;

//                $unread_count_by_group += GroupMessageReadStatusModel::unreadCountByUserIdAndGroupId($auth->user->id , $v->target_id);

            }
        }
        // 申请记录
        $unread_count_by_app = ApplicationModel::countByUserIdAndIsRead($auth->user->id , 0);
        // 推送消息（全部类型：公告等其他）
        $unread_count_by_push = PushModel::unreadCountByUserIdAndTypeAndIsRead($auth->user->id , '' , 0);
        $count = $unread_count_by_private +
            $unread_count_by_group +
            $unread_count_by_app +
            $unread_count_by_push;
        return self::success($count);
    }

    // 私聊 + 群聊的未读消息数量
    public static function unreadForSession(Auth $auth , array $param)
    {
        $session = SessionModel::getByUserId($auth->user->id);
        $unread_count_by_private = 0;
        $unread_count_by_group = 0;
        foreach ($session as $v)
        {
            if ($v->type == 'private') {
                // 检查是否开启了免打扰
                $other_id = ChatUtil::otherId($v->target_id , $v->user_id);
                $relation = FriendData::findByIdentifierAndUserIdAndFriendId($v->identifier , $v->user_id , $other_id);
                $can_notice = empty($relation) ? 1 : $relation->can_notice;
                $unread_count_by_private += $can_notice == 1 ? MessageReadStatusModel::unreadCountByUserIdAndChatId($auth->user->id , $v->target_id) : 0;

//                $unread_count_by_private += MessageReadStatusModel::unreadCountByUserIdAndChatId($auth->user->id , $v->target_id);

            }
            if ($v->type == 'group') {
                $member = GroupMemberData::findByIdentifierAndGroupIdAndUserId($auth->identifier , $v->target_id , $v->user_id);
                $can_notice = empty($member) ? 1 : $member->can_notice;
                $unread_count_by_group += $can_notice == 1 ? GroupMessageReadStatusModel::unreadCountByUserIdAndGroupId($auth->user->id , $v->target_id) : 0;

//                $unread_count_by_group += GroupMessageReadStatusModel::unreadCountByUserIdAndGroupId($auth->user->id , $v->target_id);


            }
        }
        // 推送消息（全部类型：公告等其他）
        $unread_count_by_push = PushModel::unreadCountByUserIdAndTypeAndIsRead($auth->user->id , 'system' , 0);
        return self::success($unread_count_by_private + $unread_count_by_group + $unread_count_by_push);
    }

    // 申请记录的未读消息数量
    public static function unreadForApp(Auth $auth , array $param)
    {
        // 申请记录
        $unread_count_by_app = ApplicationModel::countByUserIdAndIsRead($auth->user->id , 0);
        return self::success($unread_count_by_app);
    }

}