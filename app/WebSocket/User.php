<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/11
 * Time: 12:28
 */

namespace App\WebSocket;

class User extends Auth
{
    public function send()
    {
// 绑定用户信息
        $res = UserRedis::bindFdByUserId($this->fd , $user->identifier , $user->id);
        if ($res == false) {
            DB::rollBack();
            $this->conn->disconnect($this->fd , 500 , 'Redis 服务器挂了');
            return false;
        }
        // 创建临时群
        $group = Group::temp($this->identifier);

        // 加入群
        GroupMember::insert([
            'group_id' => $group->id ,
            'user_id'  => $user->id ,
        ]);
        // 自动分配在线客服
        $customer = User::getByIdentifierAndRole($this->identifier , 'admin');
        if (!empty($customer)) {
            DB::commit();
            // 没有工作人员
            $this->push($user->id , 'no_customer_service' , Data::noCustomerService($group , '很抱歉我们的工作人员还未上岗，您可以留言，待工作人员到岗后将第一时间回复'));
            return false;
        }
        // 检查是否存在在线的客服人员
        $online = [];
        foreach ($customer as $v)
        {
            if (!UserRedis::isOnline($this->identifier , $v)) {
                continue ;
            }
            $online[] = $v;
        }
        if (empty($online)) {
            DB::commit();
            // 不存在在线客服
            $this->push($user->id , 'no_customer_service' , Data::noCustomerService($group , '很抱歉我们的工作人员已经下线了，您可以留言，待工作人员到岗后将第一时间回复'));
            return false;
        }
        // todo 存在在线客服，负载均衡分配游客
        // todo 客服下线后，需要清空客服的负载数量
        // todo 游客下线后，需要减少客服的负载数量
        // todo 游客如果在规定时间内没有发送任何消息，那么也需要自动断线
        // todo ....以上就是完整的客服功能
        $index = mt_rand(0 , count($online - 1));
        $customer = $online[$index];
        $customer = User::findById($customer);
        GroupMember::insert([
            'user_id' => $customer ,
            'group_id' => $group->id
        ]);
        // 推送会话列表

        // 推送欢迎信息
        $this->push($this->fd , 'customer_service_joined' , [
            'group' => $group ,
            'message' => '客服 ' . $customer->nickname . ' 加入聊天室！'
        ]);
    }
}