<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/1
 * Time: 12:04
 */

namespace App\WebSocket\Util;

use App\Data\GroupMessageReadStatusData;
use App\Data\MessageReadStatusData;
use App\Model\DeleteMessageForPrivateModel;
use App\Model\GroupMemberModel;
use App\Model\GroupMessageModel;
use App\Model\GroupMessageReadStatusModel;
use App\Model\MessageModel;
use App\Model\MessageReadStatusModel;
use App\Model\UserModel;
use App\Util\ChatUtil;
use App\Util\GroupUtil;
use App\Util\MiscUtil;
use App\Util\UserUtil as TopUserUtil;

class MessageUtil extends Util
{
    /**
     * @param \App\Model\GroupMessageModel|\StdClass $group_message
     */
    public static function handleGroupMessage($group_message , int $user_id = 0)
    {
        if (empty($group_message)) {
            return;
        }
        $group_message->session_id = ChatUtil::sessionId('group', $group_message->group_id);
        if (isset($group_message->group)) {
            GroupUtil::handle($group_message->group);
            if (isset($group_message->user)) {
                if ($group_message->group->is_service == 1 && $group_message->user->role == 'admin') {
                    $name = TopUserUtil::getNameFromNicknameAndUsername($group_message->user->nickname, $group_message->user->username);
                    $group_message->user->nickname = $name;
                    $group_message->user->nickname = '客服 ' . $name;
                }
            }
        }
        if (isset($group_message->user)) {
            TopUserUtil::handle($group_message->user);
            $member = GroupMemberModel::findByUserIdAndGroupId($group_message->user_id, $group_message->group_id);
            if (!empty($member)) {
                // 特殊：群昵称
                $group_message->user->nickname = empty($member) ?
                    $group_message->user->nickname :
                    (empty($member->alias) ?
                        $group_message->user->nickname :
                        $member->alias);
            }
        }
        if ($group_message->type == 'message_set') {
            // 合并转发的消息
            $message_ids = json_decode($group_message->message, true);
            switch ($group_message->extra) {
                case 'private':
                    $messages = MessageModel::getByIds($message_ids);
                    break;
                case 'group':
                    $messages = GroupMessageModel::getByIds($message_ids);
                    break;
            }
            foreach ($messages as $v) {
                // 文件处理
                TopUserUtil::handle($v->user);
            }
            $group_message->messages = $messages;
        }
        if ($group_message->type == 'card') {
            // 名片消息
            $user_for_card = UserModel::findById($group_message->message);
            TopUserUtil::handle($user_for_card);
            $group_message->user_for_card = $user_for_card;
        }

        if (!empty($user_id)) {
            $group_message->is_read = GroupMessageReadStatusData::isReadByIdentifierAndUserIdAndGroupMessageId($group_message->identifier , $user_id , $group_message->id);
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
    public static function handleMessage($msg , int $user_id = 0 , int $other_id = 0)
    {
        if (empty($msg)) {
            return ;
        }
        $msg->session_id = ChatUtil::sessionId('private' , $msg->chat_id);
        $msg->self_is_read = MessageReadStatusData::isReadByIdentifierAndUserIdAndMessageId($msg->identifier , $user_id , $msg->id);
        $msg->other_is_read = MessageReadStatusData::isReadByIdentifierAndUserIdAndMessageId($msg->identifier , $other_id , $msg->id);
        if (isset($msg->user)) {
            TopUserUtil::handle($msg->user , $other_id);
        }
        if ($msg->type == 'message_set') {
            // 合并转发的消息
            $message_ids = json_decode($msg->message , true);
            switch ($msg->extra)
            {
                case 'private':
                    $messages = MessageModel::getByIds($message_ids);
                    break;
                case 'group':
                    $messages = GroupMessageModel::getByIds($message_ids);
                    break;
            }
            foreach ($messages as $v)
            {
                // 用户处理
                TopUserUtil::handle($v->user);
            }
            $msg->messages = $messages;
        }
        if ($msg->type == 'card') {
            // 名片消息
            $user_for_card = UserModel::findById($msg->message);
            TopUserUtil::handle($user_for_card);
            $msg->user_for_card = $user_for_card;
        }
    }

    // 删除消息
    public static function delMessageByIds($id_list)
    {
        MessageModel::delByIds($id_list);
        MessageReadStatusModel::delByMessageIds($id_list);
        DeleteMessageForPrivateModel::delByMessageIds($id_list);
    }

}