<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/11
 * Time: 17:14
 */

namespace App\WebSocket\V1\Util;


use App\WebSocket\V1\Model\DeleteMessageForGroupModel;
use App\WebSocket\V1\Model\DeleteMessageModel;
use App\WebSocket\V1\Model\GroupMemberModel;
use App\WebSocket\V1\Model\GroupMessageModel;
use App\WebSocket\V1\Model\GroupMessageReadStatusModel;
use App\WebSocket\V1\Model\MessageModel;
use App\WebSocket\V1\Model\MessageReadStatusModel;

class GroupMessageUtil extends Util
{
    public static function delete(int $group_message_id)
    {
        $message = GroupMessageModel::findById($group_message_id);
        // 删除未读消息状态
        GroupMessageReadStatusModel::delByGroupMessageId($group_message_id);
        // 从删除消息列表中删除指定类型和消息id 的记录
        DeleteMessageForGroupModel::delByGroupMessageId($group_message_id);
        // 删除消息
        GroupMessageModel::delById($group_message_id);
        // 删除 oss 云存储上的文件
        $res_type_for_message = config('business.res_type_for_message');
        if (in_array($message->type , $res_type_for_message)) {
            $iv = config('app.aes_vi');
            $msg = $message->old < 1 ? AesUtil::decrypt($message->message , $message->aes_key , $iv) : $message->message;
            OssUtil::delAll([$msg]);
        }
    }

    // 屏蔽消息
    public static function shield(string $identifier , int $user_id , string $group_id , int $group_message_id)
    {
        $group_member_count = GroupMemberModel::countByGroupId($group_id);
        $count = DeleteMessageForGroupModel::countByGroupIddAndGroupMessageId($group_id , $group_message_id);
        if ($count + 1 >= $group_member_count) {
            self::delete($group_message_id);
            return ;
        }
        DeleteMessageForGroupModel::u_insertGetId($identifier , $user_id , $group_message_id , $group_id);
    }

    // 创建 redis


}