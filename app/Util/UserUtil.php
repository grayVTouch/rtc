<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/27
 * Time: 14:10
 */

namespace App\Util;


use App\Data\BlacklistData;
use App\Data\FriendData;
use App\Data\GroupMemberData;
use App\Data\UserData;
use App\Data\UserJoinFriendOptionData;
use App\Data\UserOptionData;
use App\Model\ApplicationModel;
use App\Model\BlacklistModel;
use App\Model\FriendModel;
use App\Model\GroupMemberModel;
use App\Model\GroupMessageModel;
use App\Model\GroupModel;
use App\Model\MessageModel;
use App\Model\PushReadStatusModel;
use App\Model\SessionModel;
use App\Model\UserJoinFriendOptionModel;
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
     * @throws \Exception
     */
    public static function handle($user , int $relation_user_id = 0)
    {
        if (empty($user)) {
            return ;
        }
        $user->online = UserRedis::isOnline($user->identifier , $user->id) ? 1 : 0;
        // 用户最近一次在线时间
        $user_recent_online_timestamp = UserRedis::userRecentOnlineTimestamp($user->identifier , $user->id);
        $user->user_recent_online_timestamp = empty($user_recent_online_timestamp) ? null : $user_recent_online_timestamp;
        if (!empty($relation_user_id)) {
            $friend = FriendData::findByIdentifierAndUserIdAndFriendId($user->identifier , $relation_user_id , $user->id);
            // 黑名单
//            $user->blocked = BlacklistData::blockedByIdentifierAndUserIdAndBlockUserId($user->identifier , $relation_user_id, $user->id);
            $user->blocked = BlacklistModel::blocked($relation_user_id, $user->id);
            $user->is_friend = empty($friend) ? 0 : 1;
            // 保存用户自身设置的昵称
            $user->origin_nickname = $user->nickname;
            // 好友名称
//            $alias = FriendModel::alias($relation_user_id , $user->id);
            // 处理后的名称
            $nickname = UserUtil::getNameFromNicknameAndUsername($user->nickname , $user->username);
            $user->nickname = empty($friend) ?
                $nickname :
                (empty($friend->alias) ?
                    $nickname :
                    $friend->alias);
            $user->remarked = empty($friend) ?
                0 :
                (empty($friend->alias) ?
                    0 :
                    1);
            // 是否阅后即焚
            $user->burn = empty($friend) ? 0 : $friend->burn;
            // 检查是否置顶
            $user->top = empty($friend) ? 0 : $friend->top;
            // 是否免打扰
            $user->can_notice = empty($friend) ? 1 :$friend->can_notice;
            // 聊天背景
            $user->background = empty($friend) ? '' : $friend->background;

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
    public static function delete(string $identifier , int $user_id)
    {
        $friend_ids = FriendModel::getFriendIdByUserId($user_id);
        foreach ($friend_ids as $v)
        {
            // 删除私聊消息
            $chat_id    = ChatUtil::chatId($user_id , $v);
            // 删除私聊会话列表
            SessionModel::delByTypeAndTargetId('private' , $chat_id);

            $messages   = MessageModel::getByChatId($chat_id);
            foreach ($messages as $v2)
            {
                MessageUtil::delete($v2->id);
            }
            // 删除黑名单
            BlacklistModel::unblockUser($v , $user_id);
            BlacklistModel::unblockUser($user_id , $v);

            // 删除好友关系
            FriendData::delByIdentifierAndUserIdAndFriendId($identifier , $user_id , $v);
            FriendData::delByIdentifierAndUserIdAndFriendId($identifier , $v , $user_id);

            // 删除验证消息（无法全面删除）
            ApplicationModel::delByUserId($user_id);
        }
        $groups = GroupMemberModel::getByUserId($user_id);
        foreach ($groups as $v)
        {
            // 删除群聊会话列表
            SessionModel::delByTypeAndTargetId('group' , $v->group_id);

            if ($v->group->user_id == $user_id) {
                // 删除用户创建的群
                GroupUtil::delete($v->identifier , $v->group_id);
                continue ;
            }
            // 删除用户发布的群消息
            $group_messages = GroupMessageModel::getByGroupIdAndUserId($v->group_id , $user_id);
            foreach ($group_messages as $v2)
            {
                GroupMessageUtil::delete($v2->id);
            }
            // 删除用户加入的群
            GroupMemberData::delByIdentifierAndGroupIdAndUserId($identifier , $v->group_id , $user_id);
        }
        // 删除相关通知会话
        $push_type_for_push = config('business.push_type_for_push');
        foreach ($push_type_for_push as $v)
        {
            SessionModel::delByUserIdAndType($user_id , $v);
        }
        // 删除推送消息
        PushReadStatusModel::delByUserId($user_id);
        // 删除用户选项
        UserOptionData::delByIdentifierAndUserId($identifier , $user_id);
        // 删除用户添加方式
        $user_join_friend_option = UserJoinFriendOptionData::getByUserId($user_id);
        foreach ($user_join_friend_option as $v)
        {
            UserJoinFriendOptionData::delByIdentifierAndUserIdAndJoinFriendMethodId($v->identifier , $v->user_id , $v->join_friend_method_id);
        }
        // 删除用户
        UserData::delByIdentifierAndId($identifier , $user_id);
        // 用户下线
        WebSocket::clearRedis($user_id);
    }

    // 获取用户名
    public static function getNameFromNicknameAndUsername($nickname = '' , $username = '')
    {
        return empty($nickname) ? $username : $nickname;
    }
}