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
        // 检查当前登录类型
        if ($user->role == 'user') {
            try {
                DB::beginTransaction();
                UserAction::util_initAdvoiseGroup($user->id);
                $group = Group::advoiseGroupByUserId($user->id);
                $param['group_id'] = $group->id;
                if (!UserAction::util_allocateWaiter($user->id)) {
                    // 没有分配到客服，保存到未读消息队列
                    MessageRedis::saveUnhandleMsg($user->identifier , $user->id , $param);
                    // 检查是否已经提醒过了
                    $no_waiter_for_group = UserRedis::noWaiterForGroup($user->identifier , $group->id , true);
                    if ($no_waiter_for_group == false) {
                        $this->push($user->id , 'no_waiter' , [
                            'group' => $group ,
                            'message' => '暂无客服在线，您可以留言，我们将会第一时间回复！'
                        ]);
                        // 设置
                        UserRedis::noWaiterForGroup($user->identifier , $group->id , false);
                    }
                }
                $id = GroupMessage::insertGetId(array_unit($param , [
                    'user_id' ,
                    'group_id' ,
                    'type' ,
                    'message' ,
                    'extra' ,
                ]));
                $members = GroupMember::getUserIdByGroupId($group->id);
                foreach ($members as $v)
                {
                    // 消息读取状态
                    $is_read = $v == $user->id ? 'y' : 'n';
                    GroupMessageReadStatus::insert([
                        'user_id' => $v ,
                        'group_message_id' => $id ,
                        'is_read' => $is_read ,
                    ]);
                }
                $msg = GroupMessage::findById($id);
                // 给当前群推送消息
                $this->sendAll($members , 'group_message' , $msg);
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
            $waiter = UserRedis::groupBindWaiter($this->identifier , $param['group_id']);
            if ($waiter != $user->id) {
                // 当前群的活跃客服并非您的情况下
                return $this->error('您并非当前咨询通道的活跃客服！即：已经有客服在处理了！' , 403);
            }
            // 工作人员回复
            $id = GroupMessage::insertGetId(array_unit($param , [
                'user_id' ,
                'group_id' ,
                'type' ,
                'message' ,
                'extra' ,
            ]));
            $msg = GroupMessage::findById($id);
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
            $this->sendAll($members , 'group_message' , $msg);
            $this->success($msg);
            DB::commit();
        } catch(Exception $e) {
            DB::rollBack();
            $this->push($user->id , 'error' , (new Throwable())->exceptionJsonHandlerInDev($e , true));
        }
    }
}