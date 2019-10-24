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
use App\Util\ChatUtil;
use App\Util\GroupUtil;
use App\Util\MiscUtil;
use App\Util\UserUtil as TopUserUtil;

class MessageUtil extends Util
{
    /**
     * @param \App\Model\GroupMessageModel|\StdClass $group_message
     */
    public static function handleGroupMessage($group_message)
    {
        if (empty($group_message)) {
            return ;
        }
        $group_message->session_id = ChatUtil::sessionId('group' , $group_message->group_id);
        if (isset($group_message->group)) {
            GroupUtil::handle($group_message->group);
            if (isset($group_message->user)) {
                if ($group_message->group->is_service == 1 && $group_message->user->role == 'admin') {
                    $name = TopUserUtil::getNameFromNicknameAndUsername($group_message->user->nickname , $group_message->user->username);
                    $group_message->user->nickname = $name;
                    $group_message->user->nickname = '客服 ' . $name;
                }
            }
        }
        if (isset($group_message->user)) {
            TopUserUtil::handle($group_message->user);
        }
    }

    /**
     * 私聊消息
     *
     * @param \App\Model\MessageModel|\StdClass $msg
     * @param int $user_id
     * @param int $friend_id
     * @return null
     */
    public static function handleMessage($msg , int $user_id = 0 , int $friend_id = 0)
    {
        if (empty($msg)) {
            return ;
        }
        $msg->session_id = ChatUtil::sessionId('private' , $msg->chat_id);
        $msg->self_is_read = MessageReadStatusModel::isRead($user_id , $msg->id);
        $msg->friend_is_read = MessageReadStatusModel::isRead($friend_id , $msg->id);
        if (isset($msg->user)) {
            TopUserUtil::handle($msg->user);
        }
    }

    // 删除消息
    public static function delMessageByIds($id_list)
    {
        MessageModel::delByIds($id_list);
        MessageReadStatusModel::delByMessageIds($id_list);
    }

}