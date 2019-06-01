<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/14
 * Time: 23:37
 */

namespace App\WebSocket;


use App\Model\User;
use App\Model\Group;
use App\Model\GroupMember;
use App\Model\GroupMessage;
use App\Model\GroupMessageReadStatus;
use App\Redis\MessageRedis;
use App\Redis\UserRedis;
use App\Util\Misc;
use App\WebSocket\Action\UserAction;
use function core\array_unit;
use Core\Lib\Throwable;
use Exception;
use Illuminate\Support\Facades\DB;

class Chat extends Auth
{
    // 平台咨询：role = user 的用户进行咨询的地方
    public function advoise(array $param)
    {
        $user = $this->user;
        $param['user_id'] = $user->id;
        $param['group_id'] = $param['group_id'] ?? '';
        $param['type'] = $param['type'] ?? '';
        $param['message'] = $param['message'] ?? '';
        $param['extra'] = $param['extra'] ?? '';

        // 检查当前登录类型
        if ($user->role == 'user') {
            try {
                DB::beginTransaction();
                UserAction::util_initAdvoiseGroup($user->id);
                $group = Group::advoiseGroupByUserId($user->id);
                $param['group_id'] = $group->id;
                $session_id = Misc::sessionId('group' , $group->id);
                $group_message_id = GroupMessage::insertGetId(array_unit($param , [
                    'user_id' ,
                    'group_id' ,
                    'type' ,
                    'message' ,
                    'extra' ,
                ]));
                if (!UserAction::util_allocateWaiter($user->id)) {
                    // 没有分配到客服，保存到未读消息队列
                    MessageRedis::saveUnhandleMsg($user->identifier , $user->id , $param);
                    // 检查是否已经提醒过了
                    $no_waiter_for_group = UserRedis::noWaiterForGroup($user->identifier , $group->id , true);
                    if ($no_waiter_for_group == false) {
                        $waiter_ids = GroupMember::getWaiterIdByGroupId($group->id);
                        if (empty($waiter_ids)) {
                            // 在该群组里面没有客服，生成一个随机用户
                            $admin = User::tempAdmin($user->identifier);
                        } else {
                            $admin = User::findById($waiter_ids[0]);
                        }
                        $group_message_id_with_no_waiter = GroupMessage::insertGetId([
                            'user_id' => $admin->id ,
                            'group_id' => $param['group_id'] ,
                            'type' => 'text' ,
                            'message' => '系统通知：暂无客服在线，您可以留言，我们将会第一时间回复！' ,
                        ]);
                        $member_ids = GroupMember::getUserIdByGroupId($param['group_id']);
                        foreach ($member_ids as $v)
                        {
                            $is_read = $v == $user->id ? 'y' : 'n';
                            GroupMessageReadStatus::insert([
                                'user_id' => $v ,
                                'group_message_id' => $group_message_id_with_no_waiter ,
                                'is_read' => $is_read ,
                            ]);
                        }
                        $msg_with_no_waiter = GroupMessage::findById($group_message_id_with_no_waiter);
                        $msg_with_no_waiter->session_id = $session_id;
                        if ($group->is_service == 'y' && $msg_with_no_waiter->user->role == 'admin') {
                            $msg_with_no_waiter->user->username = '客服 ' . $msg_with_no_waiter->user->username;
                            $msg_with_no_waiter->user->nickname = '客服 ' . $msg_with_no_waiter->user->nickname;
                        }
                        // 设置
                        UserRedis::noWaiterForGroup($user->identifier , $group->id , false);
                    }
                }
                $member_ids = GroupMember::getUserIdByGroupId($group->id);
                foreach ($member_ids as $v)
                {
                    // 消息读取状态
                    $is_read = $v == $user->id ? 'y' : 'n';
                    GroupMessageReadStatus::insert([
                        'user_id' => $v ,
                        'group_message_id' => $group_message_id ,
                        'is_read' => $is_read ,
                    ]);
                }
                $msg = GroupMessage::findById($group_message_id);
                $msg->session_id = $session_id;
                DB::commit();
                // 给当前群推送消息
                $this->success($msg);
                $this->sendAll($member_ids , 'group_message' , $msg);
                if (isset($msg_with_no_waiter)) {
                    $this->pushAll($member_ids , 'group_message' , $msg_with_no_waiter);
                }
                return ;
            } catch(Exception $e) {
                DB::rollBack();
                $this->push($user->id , 'error' , (new Throwable())->exceptionJsonHandlerInDev($e , true));
            }
        }
        try {
            DB::beginTransaction();
            $waiter = UserRedis::groupBindWaiter($this->identifier , $param['group_id']);
            if ($waiter != $user->id) {
                DB::rollBack();
                // 当前群的活跃客服并非您的情况下
                return $this->error('您并非当前咨询通道的活跃客服！即：已经有客服在处理了！' , 403);
            }
            $group = Group::findById($param['group_id']);
            $session_id = Misc::sessionId('group' , $param['group_id']);
            // 工作人员回复
            $id = GroupMessage::insertGetId(array_unit($param , [
                'user_id' ,
                'group_id' ,
                'type' ,
                'message' ,
                'extra' ,
            ]));
            $msg = GroupMessage::findById($id);
            $msg->session_id = $session_id;
            if ($group->is_service == 'y' && $msg->user->role == 'admin') {
                $msg->user->username = '客服 ' . $msg->user->username;
                $msg->user->nickname = '客服 ' . $msg->user->nickname;
            }
            $members = GroupMember::getUserIdByGroupId($param['group_id']);
            foreach ($members as $v)
            {
                $is_read = $v == $user->id ? 'y' : 'n';
                GroupMessageReadStatus::insert([
                    'user_id' => $v ,
                    'group_message_id' => $id ,
                    'is_read' => $is_read ,
                ]);
            }
            // 给当前群推送消息
            $this->success($msg);
            DB::commit();
            $this->sendAll($members , 'group_message' , $msg);
        } catch(Exception $e) {
            DB::rollBack();
            $this->push($user->id , 'error' , (new Throwable())->exceptionJsonHandlerInDev($e , true));
        }
    }
}