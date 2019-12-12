<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/11
 * Time: 17:14
 */

namespace App\Util;


use App\Model\DeleteMessageModel;
use App\Model\GroupMemberModel;
use App\Model\GroupMessageModel;
use App\Model\GroupMessageReadStatusModel;
use App\Model\MessageModel;
use App\Model\MessageReadStatusModel;

class GroupMessageUtil extends Util
{
    public static function delete(int $group_message_id)
    {
        // 删除未读消息状态
        GroupMessageReadStatusModel::delByGroupMessageId($group_message_id);
        // 从删除消息列表中删除指定类型和消息id 的记录
        DeleteMessageModel::delByTypeAndMessageId('group' , $group_message_id);
        // 删除消息
        GroupMessageModel::delById($group_message_id);
    }

    // 屏蔽消息
    public static function shield(int $user_id , string $group_id , int $group_message_id)
    {
        $group_member_count = GroupMemberModel::countByGroupId($group_id);
        $count = DeleteMessageModel::countByTypeAndTargetIdAndMessageId('group' , $group_id , $group_message_id);
        if ($count + 1 >= $group_member_count) {
            self::delete($group_message_id);
            return ;
        }
        DeleteMessageModel::u_insertGetId('group' , $user_id , $group_message_id , $group_id);
    }

    // 创建 redis
}