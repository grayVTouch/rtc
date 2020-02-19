<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/1
 * Time: 11:46
 */

namespace App\WebSocket\V1\Util;

use App\WebSocket\V1\Data\FriendData;
use App\WebSocket\V1\Model\BlacklistModel;
use App\WebSocket\V1\Model\FriendModel;
use App\WebSocket\V1\Model\FundLogModel;
use App\WebSocket\V1\Model\GroupMemberModel;
use App\WebSocket\V1\Model\GroupMessageModel;
use App\WebSocket\V1\Model\GroupMessageReadStatusModel;
use App\WebSocket\V1\Model\RedPacketModel;
use App\WebSocket\V1\Model\RedPacketReceiveLogModel;
use App\WebSocket\V1\Util\PushUtil;
use App\WebSocket\V1\Model\PushReadStatusModel;
use App\WebSocket\V1\Model\UserModel;
use App\WebSocket\V1\Redis\UserRedis;
use Core\Lib\Throwable;
use Exception;
use Illuminate\Support\Facades\DB;
use App\WebSocket\V1\Model\GroupModel;
use App\WebSocket\V1\Redis\MessageRedis;
use App\WebSocket\V1\Data\GroupMemberData;
use App\WebSocket\V1\Data\UserData;
use App\WebSocket\V1\Data\UserJoinFriendOptionData;
use App\WebSocket\V1\Data\UserOptionData;
use App\WebSocket\V1\Model\ApplicationModel;
use App\WebSocket\V1\Model\MessageModel;
use App\WebSocket\V1\Model\SessionModel;
use Engine\Facade\WebSocket;

class UserUtil extends Util
{
    // 自动分配客服，已经分配到客服时返回 true；其他情况返回 false（没有客服|程序代码报错）
    public static function allocateWaiter($user_id , $group_id)
    {
        $user = UserModel::findById($user_id);
        if ($user->role != 'user') {
            return self::error("客服分配失败【{$user_id}】：不是平台用户" , 403);
        }
        $group = GroupModel::advoiseGroupByUserId($user_id);
        // 检查有没有活跃的后台客服
        $bind_waiter = UserRedis::groupBindWaiter($user->identifier , $group->id);
        if (!empty($bind_waiter)) {
            // 已经存在客服
            return self::error("客服分配失败【{$user_id}】：已经分配客服，不允许重复分配" , 403);
        }
        // 分配在线客服
        $waiter_ids = UserModel::getIdByIdentifierAndRole($user->identifier , 'admin');
        $online = [];
        foreach ($waiter_ids as $v)
        {
            if (UserRedis::isOnline($user->identifier , $v)) {
                $online[] = [
                    'user_id' => $v ,
                    'loader'  => UserRedis::numberOfReceptionsForWaiter($user->identifier , $v) ,
                ];
            }
        }
        if (empty($online)) {
            // 没有在线客服
            return self::error("客服分配失败【{$user_id}】：没有客服在线" , 404);
        }
        usort($online , function($a , $b){
            if ($a['loader'] == $b['loader']) {
                return 0;
            }
            return $a['loader'] > $b['loader'] ? 1 : -1;
        });
        $waiter = $online[0];
        if ($waiter['loader'] > config('app.number_of_receptions')) {
            // 超过客服当前接通的最大数量
            return self::error("客服分配失败【{$user_id}】：客服繁忙"  , 429);
        }
        $waiter_id = $waiter['user_id'];
        try {
            DB::beginTransaction();
            $waiter = UserModel::findById($waiter_id);
            // 存在客服
            UserRedis::groupBindWaiter($user->identifier , $group->id , $waiter->id);
            UserRedis::delNoWaiterForGroup($user->identifier , $group->id);
            if (empty(GroupMemberModel::findByUserIdAndGroupId($waiter->id , $group->id))) {
                // 不在群内，加入到聊天室
                GroupMemberModel::u_insertGetId($waiter->identifier , $waiter->id , $group->id);
            }
            $user_ids = GroupMemberModel::getUserIdByGroupId($group->id);
            // 客服自动加入消息通知
            $group_message_id = GroupMessageModel::u_insertGetId($waiter->id , $group->id , 'text' , sprintf(config('business.message')['waiter_join'] , self::getNameFromNicknameAndUsername($waiter->nickname , $waiter->username)));
            GroupMessageReadStatusModel::initByGroupMessageId($group_message_id , $group->id , $user->id);
            $msg = GroupMessageModel::findById($group_message_id);
            MessageUtil::handleGroupMessage($msg);
            DB::commit();
            PushUtil::multiple($user->identifier , $user_ids , 'group_message' , $msg);
            // 推送：刷新列表
            PushUtil::multiple($user->identifier , $user_ids , 'refresh_session');
            PushUtil::multiple($user->identifier , $user_ids , 'refresh_unread_count');
            PushUtil::multiple($user->identifier , $user_ids , 'refresh_session_unread_count');
            // 自动分配客服成功
            return self::success($waiter->id);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function initAdvoiseGroup(int $user_id) :void
    {
        $user = UserModel::findById($user_id);
        if ($user->role != 'user') {
            // 如果不是平台用户，跳过
            return ;
        }
        $group = GroupModel::advoiseGroupByUserId($user->id);
        if (!empty($group)) {
            return ;
        }
        // 为空，说明该用户并没有咨询通道
        if ($user->is_temp == 1) {
            // 临时用户：创建临时组
            $group = GroupModel::temp($user->id);
        } else {
            // 正式用户：创建组
            $group_name = sprintf('advoise-%s-%s' , $user->identifier , $user->id);
            $id = GroupModel::insertGetId([
                'identifier'    => $user->identifier ,
                'name'          => $group_name ,
                'user_id'       => $user->id ,
                'is_temp'       => 0 ,
                'is_service'    => 1 ,
            ]);
            $group = GroupModel::findById($id);
        }
        // 加入群
        GroupMemberModel::insert([
            'user_id'   => $user->id ,
            'group_id'  => $group->id ,
        ]);
    }

    // 通信未读数量：私聊/群聊
    public static function unreadCountForCommunication($user_id)
    {
        // 总：未读消息
        // 总：未读聊天消息（私聊/群聊） + 未读推送消息
        $group_unread_count = 0;
        $group_ids = GroupMemberModel::getGroupIdByUserId($user_id);
        foreach ($group_ids as $v)
        {
            $group_unread_count += GroupMessageReadStatusModel::countByUserIdAndGroupId($user_id , $v , 0);
        }
        // todo 私聊-未读消息数量
        $res = $group_unread_count;
        return $res;
    }

    // 推送未读数量
    public static function unreadCountForPush($user_id)
    {
        $count = PushReadStatusModel::unreadCountByUserId($user_id);
        return $count;
    }

    // 总：私聊/群聊/推送
    public static function unreadCount($user_id)
    {
        $group_unread_count = self::unreadCountForCommunication($user_id);
        // todo 未读群聊消息
        $unread_push_count = self::unreadCountForPush($user_id);
        $res = $group_unread_count + $unread_push_count;
        return $res;
    }


    // 创建临时用户
    public static function createTempUser(string $identifier)
    {
        return UserModel::temp($identifier);
    }

    // 消费未读消息（咨询通道）
    public static function consumeUnhandleMsg(UserModel $waiter)
    {
        if (empty($waiter)) {
            return ;
        }
        if ($waiter->role != 'admin') {
            // 不是工作人员
            return ;
        }
        try {
            DB::beginTransaction();
            $group_msg = MessageRedis::consumeUnhandleMsg($waiter->identifier);
            $push = [];
            foreach ($group_msg as $v)
            {
                if (empty(GroupMemberModel::findByUserIdAndGroupId($waiter->id , $v['group_id']))) {
                    GroupMemberModel::u_insertGetId($waiter->identifier , $waiter->id , $v['group_id']);
                    $user_ids = GroupMemberModel::getUserIdByGroupId($v['group_id']);
                    PushUtil::multiple($waiter->identifier , $user_ids , 'refresh_session');
                }
                $user_ids = GroupMemberModel::getUserIdByGroupId($v['group_id']);
                $group_message_id = GroupMessageModel::u_insertGetId($waiter->id , $v['group_id'] , 'text' , sprintf(config('business.message')['waiter_join'] , self::getNameFromNicknameAndUsername($waiter->nickname , $waiter->username)));
                GroupMessageReadStatusModel::initByGroupMessageId($group_message_id , $v['group_id'] , $waiter->id);
                $msg = GroupMessageModel::findById($group_message_id);
                MessageUtil::handleGroupMessage($msg);
                $push[] = [
                    'identifier'    => $waiter->identifier ,
                    'user_ids'      => $user_ids ,
                    'type'          => 'group_message' ,
                    'data'          => $msg
                ];
                // 绑定活跃群组
                UserRedis::groupBindWaiter($waiter->identifier , $v['group_id'] , $waiter->id);
                UserRedis::delNoWaiterForGroup($waiter->identifier , $v['group_id']);
            }
            DB::commit();
            foreach ($push as $v)
            {
                PushUtil::multiple($v['identifier'] , $v['user_ids'] , $v['type'] , $v['data']);
            }
        } catch (Exception $e) {
            DB::rollBack();
            PushUtil::single($waiter->identifier , $waiter->id , 'error' , (new Throwable())->exceptionJsonHandlerInDev($e , true));
        }
    }

    // 通知客户端没有在线客服
    public static function noWaiterTip(string $identifier , int $user_id , int $group_id)
    {
        $no_waiter_for_group = UserRedis::noWaiterForGroup($identifier , $group_id , true);
        if ($no_waiter_for_group != false) {
            // 已经提醒过了，退出
            return ;
        }
        $system_user = UserModel::systemUser($identifier);
        // 插入新消息
        $group_message_id = GroupMessageModel::u_insertGetId($system_user->id , $group_id , 'text' , '系统通知：暂无客服在线，您可以留言，我们将会第一时间回复！');
        // 初始化消息已读/未读
        GroupMessageReadStatusModel::initByGroupMessageId($group_message_id , $group_id , $user_id);
        // 找到该条信息
        $msg = GroupMessageModel::findById($group_message_id);
        // 消息处理
        MessageUtil::handleGroupMessage($msg);
        $user_ids = GroupMemberModel::getUserIdByGroupId($group_id);
        PushUtil::multiple($identifier , $user_ids , 'group_message' , $msg);
        // 是否提示过客服不存在
        UserRedis::noWaiterForGroup($identifier , $group_id , false);
    }

    /**
     * 处理用户信息
     *
     * @param \App\WebSocket\V1\Model\UserModel|\StdClass $user
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
        }
        // 删除验证消息（无法全面删除）
        ApplicationModel::delByUserId($user_id);
        ApplicationModel::delByTypeAndRelationUserId('private' , $user_id);
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
        // 删除红包相关信息
        $red_packet = RedPacketModel::getByUserId($user_id);
        $red_packet_ids = [];
        foreach ($red_packet as $v)
        {
            $red_packet_ids[] = $v->id;
        }
        RedPacketReceiveLogModel::delByRedPacketIds($red_packet_ids);
        RedPacketModel::delByUserId($user_id);

        // 删除资金记录相关信息
        FundLogModel::delByUserId($user_id);
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