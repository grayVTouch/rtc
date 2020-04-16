<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/1
 * Time: 12:04
 */

namespace App\WebSocket\V1\Util;

use App\WebSocket\V1\Data\GroupMessageReadStatusData;
use App\WebSocket\V1\Data\MessageReadStatusData;
use App\WebSocket\V1\Data\RedPacketData;
use App\WebSocket\V1\Model\DeleteMessageForPrivateModel;
use App\WebSocket\V1\Model\GroupMemberModel;
use App\WebSocket\V1\Model\GroupMessageModel;
use App\WebSocket\V1\Model\GroupMessageReadStatusModel;
use App\WebSocket\V1\Model\MessageModel;
use App\WebSocket\V1\Model\MessageReadStatusModel;
use App\WebSocket\V1\Model\RedPacketModel;
use App\WebSocket\V1\Model\UserModel;
use App\WebSocket\V1\Util\ChatUtil;
use App\WebSocket\V1\Util\GroupUtil;
use App\WebSocket\V1\Util\MiscUtil;

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
                    $name = UserUtil::getNameFromNicknameAndUsername($group_message->user->nickname, $group_message->user->username);
                    $group_message->user->nickname = $name;
                    $group_message->user->nickname = '客服 ' . $name;
                }
            }
        }
        if (isset($group_message->user)) {
            UserUtil::handle($group_message->user);
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
                UserUtil::handle($v->user);
            }
            $group_message->messages = $messages;
        }
        $message = $group_message->old > 0 ? $group_message->message : AesUtil::decrypt($group_message->message , $group_message->aes_key , config('app.aes_vi'));
        if ($group_message->type == 'card') {
            // 名片消息
            $user_for_card = UserModel::findById($group_message->message);
            UserUtil::handle($user_for_card);
            $group_message->user_for_card = $user_for_card;
        }
        if ($group_message->type == 'red_packet') {
            // 红包消息
            $red_packet = RedPacketData::findByIdentifierAndId($group_message->identifier , $message);
            RedPacketUtil::handle($red_packet , $user_id);
            $group_message->red_packet = $red_packet;
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
            UserUtil::handle($msg->user , $other_id);
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
                UserUtil::handle($v->user);
            }
            $msg->messages = $messages;
        }
        $message = $msg->old > 0 ? $msg->message : AesUtil::decrypt($msg->message , $msg->aes_key , config('app.aes_vi'));
        if ($msg->type == 'card') {
            // 名片消息
            $user_for_card = UserModel::findById($message);
            UserUtil::handle($user_for_card);
            $msg->user_for_card = $user_for_card;
        }
        if ($msg->type == 'red_packet') {
            // 红包消息
            $red_packet = RedPacketData::findByIdentifierAndId($msg->identifier , $message);
            RedPacketUtil::handle($red_packet , $user_id);
            $msg->red_packet = $red_packet;
//            $msg->red_packet = $red_packet;
        }
    }

    // 删除消息
    public static function delMessageByIds($id_list)
    {
        MessageModel::delByIds($id_list);
        MessageReadStatusModel::delByMessageIds($id_list);
        DeleteMessageForPrivateModel::delByMessageIds($id_list);
    }

    public static function delete(int $message_id)
    {
        $message = MessageModel::findById($message_id);
        // 删除未读消息状态
        MessageReadStatusModel::delByMessageId($message_id);
        // 从删除消息列表中删除指定类型和消息id 的记录
        DeleteMessageForPrivateModel::delByMessageId($message_id);
        // 删除消息
        MessageModel::delById($message_id);
        // todo 新增：内容，删除亚马逊云存储上的文件
        $res_type_for_message = config('business.res_type_for_message');
//        print_r($message_type_for_oss);
//        var_dump($message->type);
        if (in_array($message->type , $res_type_for_message)) {
            $iv = config('app.aes_vi');
            $msg = $message->old < 1 ? AesUtil::decrypt($message->message , $message->aes_key , $iv) : $message->message;
            OssUtil::delAll([$msg]);
        }


    }

    public static function delOssFile($message)
    {
        $iv = config('app.aes_vi');
        $msg = $message->old < 1 ? AesUtil::decrypt($message->message , $message->aes_key , $iv) : $message->message;
        OssUtil::delAll([$msg]);
    }

    // 屏蔽消息
    public static function shield(string $identifier , int $user_id , string $chat_id , int $message_id)
    {
        $count = DeleteMessageForPrivateModel::countByChatIdAndMessageId($chat_id , $message_id);
        if ($count + 1 >= 2) {
            // 计数超过两个，将该消息彻底删除
            self::delete($message_id);
            return ;
        }
        DeleteMessageForPrivateModel::u_insertGetId($identifier , $user_id , $message_id , $chat_id);
    }
}