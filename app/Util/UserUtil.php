<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/27
 * Time: 14:10
 */

namespace App\Util;


use App\Model\ApplicationModel;
use App\Model\BlacklistModel;
use App\Model\FriendModel;
use App\Model\GroupMemberModel;
use App\Model\GroupMessageModel;
use App\Model\GroupModel;
use App\Model\MessageModel;
use App\Model\UserModel;
use App\Model\UserOptionModel;
use App\Redis\UserRedis;
use Engine\Facade\WebSocket;
use Exception;

class UserUtil extends Util
{
    /**
     * 处理用户信息
     *
     * @param \App\Model\UserModel|\StdClass $user
     */
    public static function handle($user , int $relation_user_id = 0)
    {
        if (empty($user)) {
            return ;
        }
        $user->online = UserRedis::isOnline($user->identifier , $user->id) ? 1 : 0;
        if (!empty($relation_user_id)) {
            // 黑名单
            $user->blocked = BlacklistModel::blocked($relation_user_id, $user->id) ? 1 : 0;
            $user->is_friend = FriendModel::isFriend($relation_user_id , $user->id) ? 1 : 0;
            // 保存用户自身设置的昵称
            $user->origin_nickname = $user->nickname;
            // 好友名称
            $alias = FriendModel::alias($relation_user_id , $user->id);
            // 处理后的名称
            $user->nickname = empty($alias) ? $user->nickname : $alias;

        }
    }

    // 检查手机号码是否一致
    public static function isSamePhoneWithAreaCode($area_code_for_origin , $phone_for_origin , $area_code_for_compare , $phone_for_compare)
    {
        $area_code_for_origin = rtrim($area_code_for_origin , '+');
        $area_code_for_compare = rtrim($area_code_for_origin , '+');
        $full_phone_for_origin = sprintf('%s%s' , $area_code_for_origin , $phone_for_origin);
        $full_phone_for_compare = sprintf('%s%s' , $area_code_for_compare , $phone_for_compare);
        return $full_phone_for_origin == $full_phone_for_compare;
    }

    // 建立客户端连接 和 用户id 的映射
    public static function mapping(string $identifier , int $user_id , int $fd)
    {
        UserRedis::userIdMappingFd($identifier , $user_id , $fd);
        UserRedis::fdMappingUserId($identifier , $fd , $user_id);
    }

    // 上下线通知
    public static function onlineStatusChange(string $identifier , int $user_id , string $status)
    {
        $online_status = config('business.online_status');
        if (!in_array($status , $online_status)) {
            throw new Exception('不支持的状态，当前受支持的状态有：' . implode(',' , $online_status));
        }
        // 表示当前用户id已经完全下线了
        $friend_ids = FriendModel::getFriendIdByUserId($user_id);
        $groups = GroupMemberModel::getByUserId($user_id);
        // 刷新群成员列表
        foreach ($groups as $v)
        {
            $user_ids = GroupMemberModel::getUserIdByGroupId($v->group_id);
            $user_ids = array_diff($user_ids , [$user_id]);
            PushUtil::multiple($identifier , $user_ids , 'refresh_group_member');
        }
        // 刷新好友列表
        PushUtil::multiple($identifier , $friend_ids , 'refresh_friend');
        // 通知用户刷新用户信息
        switch ($status)
        {
            case 'online':
                PushUtil::multiple($identifier , $friend_ids , 'online' , $user_id);
                break;
            case 'offline':
                PushUtil::multiple($identifier , $friend_ids , 'offline' , $user_id);
                break;
        }
    }

    // 删除好友关系
    public static function deleteFriendRelation(int $user_id)
    {

    }

    // 删除用户
    public static function delete(int $user_id)
    {
        $friend_ids = FriendModel::getFriendIdByUserId($user_id);
        foreach ($friend_ids as $v)
        {
            // 删除私聊消息
            $chat_id    = ChatUtil::chatId($user_id , $v);
            $messages   = MessageModel::getByChatId($chat_id);
            foreach ($messages as $v2)
            {
                MessageUtil::delete($v2->id);
            }
            // 删除黑名单
            BlacklistModel::unblockUser($v , $user_id);
            BlacklistModel::unblockUser($user_id , $v);

            // 删除好友关系
            FriendModel::delByUserIdAndFriendId($user_id , $v);
            FriendModel::delByUserIdAndFriendId($v , $user_id);

            // 删除验证消息（无法全面删除）
            ApplicationModel::delByUserId($user_id);
        }
        $groups = GroupMemberModel::getByUserId($user_id);
        foreach ($groups as $v)
        {
            if ($v->group->user_id == $user_id) {
                // 删除用户创建的群
                GroupUtil::delete($v->group_id);
                continue ;
            }
            // 删除用户发布的言论
            $group_messages = GroupMessageModel::getByGroupIdAndUserId($v->group_id , $user_id);
            foreach ($group_messages as $v2)
            {
                GroupMessageUtil::delete($v2->id);
            }
            // 删除用户加入的群
            GroupMemberModel::delByUserIdAndGroupId($user_id , $v->group_id);
        }
        // 删除用户选项
        UserOptionModel::delByUserId($user_id);
        // 删除用户
        UserModel::delById($user_id);
        WebSocket::clearRedis($user_id);
    }
}