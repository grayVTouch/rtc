<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/11
 * Time: 17:14
 */

namespace App\Util;


use App\Model\DeleteMessageModel;
use App\Model\MessageModel;
use App\Model\MessageReadStatusModel;

class MessageUtil extends Util
{
    public static function delete(int $message_id)
    {
        // 删除未读消息状态
        MessageReadStatusModel::delByMessageId($message_id);
        // 从删除消息列表中删除指定类型和消息id 的记录
        DeleteMessageModel::delByTypeAndMessageId('private' , $message_id);
        // 删除消息
        MessageModel::delById($message_id);
    }

    // 屏蔽消息
    public static function shield(int $user_id , string $chat_id , int $message_id)
    {
        $count = DeleteMessageModel::countByTypeAndTargetIdAndMessageId('private' , $chat_id , $message_id);
        if ($count + 1 >= 2) {
            // 计数超过两个，将该消息彻底删除
            self::delete($message_id);
            return ;
        }
        DeleteMessageModel::u_insertGetId('private' , $user_id , $message_id , $chat_id);
    }
}