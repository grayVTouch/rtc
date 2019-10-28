<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/20
 * Time: 13:52
 */

namespace App\WebSocket\Action;


use App\Model\ApplicationModel;
use App\Model\GroupMessageReadStatusModel;
use App\Model\MessageReadStatusModel;
use App\Model\PushModel;
use App\WebSocket\Auth;

class UnreadAction extends Action
{

    public static function unread(Auth $auth , array $param)
    {
        // 私聊 + 群聊 + 申请记录 + 所有未读消息数量
        $unread_count_by_private = MessageReadStatusModel::countByUserIdAndIsRead($auth->user->id , 0);
        // 群聊
        $unread_count_by_group = GroupMessageReadStatusModel::countByUserIdAndIsRead($auth->user->id , 0);
        // 申请记录
        $unread_count_by_app = ApplicationModel::countByUserIdAndIsRead($auth->user->id , 0);
        // 推送消息（全部类型：公告等其他）
        $unread_count_by_push = PushModel::unreadCountByUserIdAndType($auth->user->id);
        $count = $unread_count_by_private +
            $unread_count_by_group +
            $unread_count_by_app +
            $unread_count_by_push;
        return self::success($count);
    }

    // 私聊 + 群聊的未读消息数量
    public static function unreadForSession(Auth $auth , array $param)
    {
        // 私聊 + 群聊 + 申请记录 + 所有未读消息数量
        $unread_count_by_private = MessageReadStatusModel::countByUserIdAndIsRead($auth->user->id , 0);
        // 群聊
        $unread_count_by_group = GroupMessageReadStatusModel::countByUserIdAndIsRead($auth->user->id , 0);
        return self::success($unread_count_by_private + $unread_count_by_group);
    }

    // 申请记录的未读消息数量
    public static function unreadForApp(Auth $auth , array $param)
    {
        // 申请记录
        $unread_count_by_app = ApplicationModel::countByUserIdAndIsRead($auth->user->id , 0);
        return self::success($unread_count_by_app);
    }

}