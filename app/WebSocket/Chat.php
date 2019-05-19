<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/14
 * Time: 23:37
 */

namespace App\WebSocket;


use App\Model\Group;
use App\Model\GroupMember;
use App\Model\GroupMessage;
use App\Model\GroupMessageReadStatus;
use App\Redis\MessageRedis;
use App\Redis\UserRedis;
use function core\array_unit;
use Exception;
use Illuminate\Support\Facades\DB;

class Chat extends Auth
{
    // 平台咨询：role = user 的用户进行咨询的地方
    public function advoise(array $data)
    {
        $user = $this->user;
        $data['user_id'] = $user->id;
        // 检查当前登录类型
        if ($user->role == 'user') {
            try {
                DB::beginTransaction();
                // 检查用户是否是否是临时用户
                if ($user->is_temp == 'y') {
                    // 临时用户
                    // 检查是否已经加入过临时组
                    if (!isset($data['group_id']) || empty($data['group_id'])) {
                        // 创建临时组
                        $group = Group::temp($this->identifier);
                        $data['group_id'] = $group->id;
                        // 加入群
                        GroupMember::insert([
                            'user_id'   => $user->id ,
                            'group_id'  => $group->id ,
                        ]);
                        // 推送：更新群信息
                        $this->push($user->id , 'refresh_group_for_advoise' , $group);
                    } else {
                        $group = Group::findById($data['group_id']);
                        if (empty($group)) {
                            DB::rollBack();
                            $this->error('未找到 group_id = ' . $data['group_id'] . '对应群信息' , 404);
                            return ;
                        }
                    }
                    $id = GroupMessage::insertGetId(array_unit($data , [
                        'user_id' ,
                        'group_id' ,
                        'type' ,
                        'message' ,
                        'extra' ,
                    ]));
                    // 发送方：消息读取状态
                    GroupMessageReadStatus::insert([
                        'user_id' => $user->id ,
                        'group_message_id' => $id ,
                        'is_read' => 'y' ,
                    ]);
                    $members = GroupMember::getUserIdByGroupId($data['group_id']);
                    $members = array_diff($members , [$user->id]);
                    foreach ($members as $v)
                    {
                        // 接收方：消息读取状态
                        GroupMessageReadStatus::insert([
                            'user_id' => $v ,
                            'group_message_id' => $id ,
                            'is_read' => 'n' ,
                        ]);
                    }
                    // 检查有没有活跃的后台客服
                    $user_ids = GroupMember::getUserIdByGroupId($group->id);
                    $bind_waiter = UserRedis::groupBindWaiter($this->identifier , $group->id);
                    if (empty($bind_waiter)) {
                        // 没有分配在线客服
                        $waiters = GroupMember::getWaiterIdByGroupId($group->id);
                        if (UserRedis::hasOnline($this->identifier , $waiters) && ($waiter = UserRedis::allocateWaiter($this->identifier)) != false) {
                            // 存在客服
                            UserRedis::groupBindWaiter($this->identifier , $group->id , $waiter);
                            UserRedis::delNoWaiterForGroup($this->identifier , $group->id);
                            // 加入到聊天室
                            if (empty(GroupMember::findByUserIdAndGroupId($waiter , $group->id))) {
                                GroupMember::insert([
                                    'user_id' => $waiter ,
                                    'group_id' => $group->id
                                ]);
                                $user_ids[] = $waiter;
                                // 推送：刷新列表
                                $this->pushAll($user_ids , 'refresh_session');
                            }
                        } else {
                            // 没有在线客服可分配，保存到未读消息队列
                            MessageRedis::saveUnhandleMsg($this->identifier , $user->id , $data);
                            // 检查是否已经提醒过了
                            $no_waiter_for_group = UserRedis::noWaiterForGroup($this->identifier , $group->id , true);
                            if ($no_waiter_for_group == false) {
                                $this->push($user->id , 'no_waiter' , [
                                    'group' => $group ,
                                    'message' => '暂无客服在线，您可以留言，我们将会第一时间回复！'
                                ]);
                                // 设置
                                UserRedis::noWaiterForGroup($this->identifier , $group->id , false);
                            }
                        }
                    }
                    $msg = GroupMessage::findById($id);
                    // 给当前群推送消息
                    $this->sendAll($user_ids , 'group_message' , $msg);
                    $this->success($msg);
                    DB::commit();
                    return ;
                }
                // 平台正式用户
                $group_name = sprintf('advoise-%s-%s' , $this->identifier , $user->id);
                if (!isset($data['group_id']) || empty($data['group_id'])) {
                    // 群不存在，创建
                    $id = Group::insertGetId([
                        'identifier' => $this->identifier ,
                        'name' => $group_name ,
                        'user_id' => $user->id ,
                        'is_temp' => 'n' ,
                        'is_service' => 'y' ,
                    ]);
                    $group = Group::findById($id);
                    // 加入群
                    GroupMember::insert([
                        'user_id' => $user->id ,
                        'group_id' => $group->id ,
                    ]);
                    $data['group_id'] = $group->id;
                    // 推送：更新群信息
                    $this->push($user->id , 'refresh_group_for_advoise' , $group);
                } else {
                    $group = Group::findById($data['group_id']);
                    if (empty($group)) {
                        DB::rollBack();
                        $this->error('未找到 group_id = ' . $data['group_id'] . '对应群信息' , 404);
                        return ;
                    }
                }
                $id = GroupMessage::insertGetId(array_unit($data , [
                    'user_id' ,
                    'group_id' ,
                    'type' ,
                    'message' ,
                    'extra' ,
                ]));
                // 发送方：消息读取状态
                GroupMessageReadStatus::insert([
                    'user_id' => $user->id ,
                    'group_message_id' => $id ,
                    'is_read' => 'y' ,
                ]);
                $members = GroupMember::getUserIdByGroupId($data['group_id']);
                $members = array_diff($members , [$user->id]);
                foreach ($members as $v)
                {
                    // 接收方：消息读取状态
                    GroupMessageReadStatus::insert([
                        'user_id' => $v ,
                        'group_message_id' => $id ,
                        'is_read' => 'n' ,
                    ]);
                }
                // 检查有没有活跃的后台客服
                $user_ids = GroupMember::getUserIdByGroupId($group->id);
                $bind_waiter = UserRedis::groupBindWaiter($this->identifier , $group->id);
                if (empty($bind_waiter)) {
                    // 没有分配在线客服
                    $waiters = GroupMember::getWaiterIdByGroupId($group->id);
                    if (UserRedis::hasOnline($this->identifier , $waiters) && ($waiter = UserRedis::allocateWaiter($this->identifier)) != false) {
                        // 存在客服
                        UserRedis::groupBindWaiter($this->identifier , $group->id , $waiter);
                        // 加入到聊天室
                        if (empty(GroupMember::findByUserIdAndGroupId($waiter , $group->id))) {
                            GroupMember::insert([
                                'user_id' => $waiter ,
                                'group_id' => $group->id
                            ]);
                            $user_ids[] = $waiter;
                            // 推送：刷新列表
                            $this->pushAll($user_ids , 'refresh_session');
                        }
                    } else {
                        // 没有在线客服可分配，保存到未读消息队列
                        MessageRedis::saveUnhandleMsg($this->identifier , $user->id , $data);
                        // 检查是否已经提醒过了
                        $no_waiter_for_group = UserRedis::noWaiterForGroup($this->identifier , $group->id , true);
                        if ($no_waiter_for_group == false) {
                            $this->push($user->id , 'no_waiter' , [
                                'group' => $group ,
                                'message' => '暂无客服在线，您可以留言，我们将会第一时间回复！'
                            ]);
                            // 设置
                            UserRedis::noWaiterForGroup($this->identifier , $group->id , false);
                        }
                    }
                }
                $msg = GroupMessage::findById($id);
                // 给当前群推送消息
                $this->sendAll($user_ids , 'group_message' , $msg);
                $this->success($msg);
                DB::commit();
                return ;
            } catch(Exception $e) {
                DB::rollBack();
                return $this->push($user->id , 'error' , $e);
            }
        }
        try {
            DB::beginTransaction();
            $waiter = UserRedis::groupBindWaiter($this->identifier , $data['group_id']);
            if ($waiter != $user->id) {
                // 当前群的活跃客服并非您的情况下
                return $this->error('您并非当前咨询通道的活跃客服！即：已经有客服在处理了！' , 403);
            }
            $user_ids = GroupMember::getUserIdByGroupId($data['group_id']);
            // 工作人员回复
            $id = GroupMessage::insertGetId(array_unit($data , [
                'user_id' ,
                'group_id' ,
                'type' ,
                'message' ,
                'extra' ,
            ]));
            // 发送方：消息读取状态
            GroupMessageReadStatus::insert([
                'user_id' => $user->id ,
                'group_message_id' => $id ,
                'is_read' => 'y' ,
            ]);
            $members = GroupMember::getUserIdByGroupId($data['group_id']);
            $members = array_diff($members , [$user->id]);
            foreach ($members as $v)
            {
                // 接收方：消息读取状态
                GroupMessageReadStatus::insert([
                    'user_id' => $v ,
                    'group_message_id' => $id ,
                    'is_read' => 'n' ,
                ]);
            }
            $msg = GroupMessage::findById($id);
            // 给当前群推送消息
            $this->sendAll($user_ids , 'group_message' , $msg);
            return $this->success($msg);
            DB::commit();
        } catch(Exception $e) {
            DB::rollBack();
            return $this->push($user->id , 'error' , $e);
        }
    }
}