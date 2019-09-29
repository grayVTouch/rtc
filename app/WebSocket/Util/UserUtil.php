<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/1
 * Time: 11:46
 */

namespace App\WebSocket\Util;

use App\Model\GroupMemberModel;
use App\Model\GroupMessageModel;
use App\Model\GroupMessageReadStatusModel;
use App\Util\PushUtil;
use App\Model\PushReadStatusModel;
use App\Model\UserModel;
use App\Redis\UserRedis;
use App\WebSocket\Base;
use Core\Lib\Throwable;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Model\GroupModel;
use App\Redis\MessageRedis;

class UserUtil extends Util
{
    // 自动分配客服，已经分配到客服时返回 true；其他情况返回 false（没有客服|程序代码报错）
    public static function allocateWaiter($user_id)
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
                GroupMemberModel::u_insertGetId($waiter->id , $group->id);
            }
            $user_ids = GroupMemberModel::getUserIdByGroupId($group->id);
            $group_message_id = GroupMessageModel::u_insertGetId($waiter->id , $group->id , 'text' , sprintf(config('business.message')['waiter_join'] , $waiter->username));
            GroupMessageReadStatusModel::initByGroupMessageId($group_message_id , $group->id , $user->id);
            $msg = GroupMessageModel::findById($group_message_id);
            MessageUtil::handleGroupMessage($msg);
            DB::commit();
            PushUtil::multiple($user->identifier , $user_ids , 'group_message' , $msg);
            // 推送：刷新列表
            PushUtil::multiple($user->identifier , $user_ids , 'refresh_session');
            // 自动分配客服成功
            return self::success($waiter->id);
        } catch(Exception $e) {
            DB::rollBack();
            PushUtil::single($user->identifier , $user->id , 'error' , (new Throwable())->exceptionJsonHandlerInDev($e , true));
            return self::error((new Throwable())->exceptionJsonHandlerInDev($e , true) , 500);
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
                    GroupMemberModel::u_insertGetId($waiter->id , $v['group_id']);
                    $user_ids = GroupMemberModel::getUserIdByGroupId($v['group_id']);
                    PushUtil::multiple($waiter->identifier , $user_ids , 'refresh_session');
                }
                $user_ids = GroupMemberModel::getUserIdByGroupId($v['group_id']);
                $group_message_id = GroupMessageModel::u_insertGetId($waiter->id , $v['group_id'] , 'text' , sprintf(config('business.message')['waiter_join'] , $waiter->username));
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
        $waiter_ids = GroupMemberModel::getWaiterIdByGroupId($group_id);
        if (empty($waiter_ids)) {
            // 在该群组里面没有客服，生成一个随机用户
            $admin = UserModel::tempAdmin($identifier);
        } else {
            $admin = UserModel::findById($waiter_ids[0]);
        }
        // 插入新消息
        $group_message_id = GroupMessageModel::u_insertGetId($admin->id , $group_id , 'text' , '系统通知：暂无客服在线，您可以留言，我们将会第一时间回复！');
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

    public static function isOnline()
    {

    }
}