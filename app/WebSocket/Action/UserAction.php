<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 21:54
 */

namespace App\WebSocket\Action;


use App\Model\Group;
use App\Model\GroupMember;
use App\Model\GroupMessage;
use App\Model\GroupMessageReadStatus;
use App\Util\Misc;
use App\Util\Push;
use App\Model\PushReadStatus;
use App\Model\User;
use App\Redis\UserRedis;
use App\WebSocket\Auth;
use App\WebSocket\Base;
use Core\Lib\Throwable;
use Exception;
use Illuminate\Support\Facades\DB;

class UserAction extends Action
{
    // 自动分配客服，已经分配到客服时返回 true；其他情况返回 false（没有客服|程序代码报错）
    public static function util_allocateWaiter($user_id) :bool
    {
        $user = User::findById($user_id);
        $group = Group::advoiseGroupByUserId($user_id);
        // 检查有没有活跃的后台客服
        $bind_waiter = UserRedis::groupBindWaiter($user->identifier , $group->id);
        if (!empty($bind_waiter)) {
            // 已经存在客服
            return true;
        }
        // 没有分配在线客服
        $waiters = GroupMember::getWaiterIdByGroupId($group->id);
        if (!UserRedis::hasOnline($user->identifier , $waiters)) {
            // 没有在线客服
            return false;
        }
        $waiter_id = UserRedis::allocateWaiter($user->identifier);
        if ($waiter_id === false) {
            // 客服繁忙
            return false;
        }
        try {
            DB::beginTransaction();
            $waiter = User::findById($waiter_id);
            // 存在客服
            UserRedis::groupBindWaiter($user->identifier , $group->id , $waiter->id);
            UserRedis::delNoWaiterForGroup($user->identifier , $group->id);
            // 加入到聊天室
            if (empty(GroupMember::findByUserIdAndGroupId($waiter->id , $group->id))) {
                GroupMember::insert([
                    'user_id' => $waiter->id ,
                    'group_id' => $group->id
                ]);
            }
            $user_ids = GroupMember::getUserIdByGroupId($group->id);
            $group_message_id = GroupMessage::insertGetId([
                'user_id' => $waiter->id ,
                'group_id' => $group->id ,
                'type' => 'text' ,
                'message' => sprintf('系统通知：您好，客服 【%s】 很高兴为您服务' , $waiter->username) ,
            ]);
            foreach ($user_ids as $v)
            {
                $is_read = $v == $waiter->id ? 'y' : 'n';
                GroupMessageReadStatus::insert([
                    'user_id' => $v ,
                    'group_message_id' => $group_message_id ,
                    'is_read' => $is_read
                ]);
            }
            $msg = GroupMessage::findById($group_message_id);
            $msg->session_id = Misc::sessionId('group' , $group->id);
            if ($group->is_service == 'y' && $msg->user->role == 'admin') {
                $msg->user->username = '客服 ' . $msg->user->username;
                $msg->user->nickname = '客服 ' . $msg->user->nickname;
            }
            DB::commit();
            Push::multiple($user->identifier , $user_ids , 'group_message' , $msg);
            // 推送：刷新列表
            Push::multiple($user->identifier , $user_ids , 'refresh_session');
            // 自动分配客服成功
            return true;
        } catch(Exception $e) {
            DB::rollBack();
            Push::single($user->identifier , $user->id , 'error' , (new Throwable())->exceptionJsonHandlerInDev($e , true));
            return false;
        }
    }

    public static function util_initAdvoiseGroup(int $user_id) :void
    {
        $user = User::findById($user_id);
        if ($user->role != 'user') {
            // 如果不是平台用户，跳过
            return ;
        }
        $group = Group::advoiseGroupByUserId($user->id);
        if (!empty($group)) {
            return ;
        }
        // 为空，说明该用户并没有咨询通道
        if ($user->is_temp == 'y') {
            // 临时用户：创建临时组
            $group = Group::temp($user->identifier);
        } else {
            // 正式用户：创建组
            $group_name = sprintf('advoise-%s-%s' , $user->identifier , $user->id);
            $id = Group::insertGetId([
                'identifier' => $user->identifier ,
                'name' => $group_name ,
                'user_id' => $user->id ,
                'is_temp' => 'n' ,
                'is_service' => 'y' ,
            ]);
            $group = Group::findById($id);
        }
        // 加入群
        GroupMember::insert([
            'user_id'   => $user->id ,
            'group_id'  => $group->id ,
        ]);
        // 推送：更新群信息
        // Push::single($user->identifier , $user->id , 'refresh_group_for_advoise' , $group);
    }

    // 咨询通道绑定的群信息
    public static function groupForAdvoise(Auth $app)
    {
        $group = Group::advoiseGroupByUserId($app->user->id);
        return self::success($group);
    }

    // 总：未读消息
    public static function util_unreadCount($user_id)
    {
        // 总：未读消息
        // 总：未读聊天消息（私聊/群聊） + 未读推送消息
        $group_unread_count = 0;
        $group_ids = GroupMember::getGroupIdByUserId($user_id);
        foreach ($group_ids as $v)
        {
            $group_unread_count += GroupMessageReadStatus::unreadCountByUserIdAndGroupId($user_id , $v);
        }
        $push_unread_count = PushReadStatus::unreadCountByUserId($user_id);
        $res = $group_unread_count + $push_unread_count;
        return self::success($res);
    }
}