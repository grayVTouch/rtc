<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 10:21
 */

namespace App\WebSocket\Action;

use App\Model\User;
use App\Model\Group;
use App\Model\GroupMember;
use App\Model\GroupMessage;
use App\Model\GroupMessageReadStatus;
use App\Redis\MessageRedis;
use App\Redis\UserRedis;
use App\Util\Misc;
use App\WebSocket\Util\MessageUtil;
use App\WebSocket\Util\UserUtil;
use function core\array_unit;
use Core\Lib\Throwable;
use Exception;
use Illuminate\Support\Facades\DB;

use App\WebSocket\Auth;

class ChatAction extends Action
{
    public static function advoise(Auth $auth , array $param)
    {
        $user = $auth->user;
        // 检查当前登录类型
        if ($user->role == 'user') {
            try {
                DB::beginTransaction();
                $group = Group::advoiseGroupByUserId($user->id);
                $param['group_id'] = $group->id;
                $group_message_id = GroupMessage::u_insertGetId($param['user_id'] , $param['group_id'] , $param['type'] , $param['message'] , $param['extra']);
                $bind_waiter = UserRedis::groupBindWaiter($user->identifier , $group->id);
                if (empty($bind_waiter)) {
                    // 没有绑定客服的情况下
                    $allocate = UserUtil::allocateWaiter($user->id);
                    if ($allocate['code'] != 200) {
                        // todo 调试
                        var_dump($allocate['data']);
                        // 没有分配到客服，保存到未读消息队列
                        MessageRedis::saveUnhandleMsg($user->identifier , $user->id , $param);
                        // 通知客户端没有客服
                        UserUtil::noWaiterTip($auth->identifier , $user->id , $group->id);
                    }
                }
                // 初始化消息已读/未读
                GroupMessageReadStatus::initByGroupMessageId($group_message_id , $param['group_id'] , $user->id);
                $user_ids = GroupMember::getUserIdByGroupId($param['group_id']);
                // 找到该条消息
                $msg = GroupMessage::findById($group_message_id);
                // 处理消息
                MessageUtil::handleGroupMessage($msg);
                DB::commit();
                $auth->sendAll($user_ids , 'group_message' , $msg);
                if (isset($msg_with_no_waiter)) {
                    // 没有客服
                    $auth->pushAll($user_ids , 'group_message' , $msg_with_no_waiter);
                }
                return self::success($msg);
            } catch(Exception $e) {
                DB::rollBack();
                $auth->push($user->id , 'error' , (new Throwable())->exceptionJsonHandlerInDev($e , true));
            }
        }
        try {
            DB::beginTransaction();
            // 检查当前群组是否绑定当前用户
            $waiter = UserRedis::groupBindWaiter($auth->identifier , $param['group_id']);
            if ($waiter != $user->id) {
                DB::rollBack();
                // 当前群的活跃客服并非您的情况下
                return self::error('您并非当前咨询通道的活跃客服！' , 403);
            }
            // 工作人员回复
            $group_message_id = GroupMessage::u_insertGetId($param['user_id'] , $param['group_id'] , $param['type'] , $param['message'] , $param['extra']);
            $msg = GroupMessage::findById($group_message_id);
            MessageUtil::handleGroupMessage($msg);
            GroupMessageReadStatus::initByGroupMessageId($group_message_id , $param['group_id'] , $user->id);
            $user_ids = GroupMember::getUserIdByGroupId($param['group_id']);
            DB::commit();
            $auth->sendAll($user_ids , 'group_message' , $msg);
            return self::success($msg);
        } catch(Exception $e) {
            DB::rollBack();
            $auth->push($user->id , 'error' , (new Throwable())->exceptionJsonHandlerInDev($e , true));
        }
    }
}