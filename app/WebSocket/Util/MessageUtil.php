<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/1
 * Time: 12:04
 */

namespace App\WebSocket\Util;

use App\Model\MessageModel;
use App\Model\MessageReadStatusModel;
use App\Util\MiscUtil;

class MessageUtil extends Util
{
    public static function handleGroupMessage($group_message)
    {
        if (empty($group_message)) {
            return ;
        }
        $group_message->session_id = MiscUtil::sessionId('group' , $group_message->group_id);
        if (!isset($group_message->group)) {
            return ;
        }
        if (!isset($group_message->user)) {
            return ;
        }
        if ($group_message->group->is_service != 1 || $group_message->user->role != 'admin') {
            return ;
        }
        $group_message->user->nickname = empty($group_message->user->nickname) ? $group_message->user->username : $group_message->user->nickname;
        $group_message->user->nickname = '客服 ' . $group_message->user->nickname;
    }

    /**
     * 私聊消息
     *
     * @param MessageModel|null $msg
     * @param int $user_id
     * @param int $friend_id
     * @return null
     */
    public static function handleMessage($msg , int $user_id , int $friend_id)
    {
        if (empty($msg)) {
            return ;
        }
        $msg->self_is_read      = MessageReadStatusModel::isRead($user_id , $msg->id);
        $msg->friend_is_read    = MessageReadStatusModel::isRead($friend_id , $msg->id);
    }
}