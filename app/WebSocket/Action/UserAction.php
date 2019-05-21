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
use App\Util\Push;
use App\Model\User;
use App\Redis\UserRedis;
use Exception;

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
        $waiter = UserRedis::allocateWaiter($user->identifier);
        if ($waiter === false) {
            // 客服繁忙
            return false;
        }
        // 存在客服
        UserRedis::groupBindWaiter($user->identifier , $group->id , $waiter);
        UserRedis::delNoWaiterForGroup($user->identifier , $group->id);
        // 加入到聊天室
        if (empty(GroupMember::findByUserIdAndGroupId($waiter , $group->id))) {
            GroupMember::insert([
                'user_id' => $waiter ,
                'group_id' => $group->id
            ]);
            $user_ids = GroupMember::getUserIdByGroupId($group->id);
            // 推送：刷新列表
            Push::multiple($user->identifier , $user_ids , 'refresh_session');
        }
        // 自动分配客服成功
        return true;
    }

    public static function util_initAdvoiseGroup(int $user_id) :void
    {
        $user = User::findById($user_id);
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
        Push::single($user->identifier , $user->id , 'refresh_group_for_advoise' , $group);
    }
}