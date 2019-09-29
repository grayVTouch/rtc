<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 10:21
 */

namespace App\WebSocket\Action;

use App\Model\FriendModel;
use App\Model\MessageModel;
use App\Model\MessageReadStatusModel;
use App\Model\GroupModel;
use App\Model\GroupMemberModel;
use App\Model\GroupMessageModel;
use App\Model\GroupMessageReadStatusModel;
use App\Redis\MessageRedis;
use App\Redis\UserRedis;
use App\Util\ChatUtil;
use App\WebSocket\Util\MessageUtil;
use App\WebSocket\Util\UserUtil;
use function core\array_unit;
use Core\Lib\Throwable;
use Core\Lib\Validator;
use Exception;
use Illuminate\Support\Facades\DB;
use App\WebSocket\Auth;
use function WebSocket\ws_config;

class ChatAction extends Action
{
    /**
     * 消息发送-平台咨询-文本
     *
     * @param Auth $auth
     * @param array $param
     * @return array
     */
    public static function advoise(Auth $auth , $type , array $param)
    {
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
            'message' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $user = $auth->user;
        $param['user_id']   = $user->id;
        // 检查当前登录类型
        if ($user->role == 'user') {
            try {
                DB::beginTransaction();
                $group = GroupModel::advoiseGroupByUserId($user->id);
                $param['group_id'] = $group->id;
                $group_message_id = GroupMessageModel::u_insertGetId($param['user_id'] , $param['group_id'] , $type , $param['message'] , $param['extra']);
                $bind_waiter = UserRedis::groupBindWaiter($user->identifier , $group->id);
                if (empty($bind_waiter)) {
                    // 没有绑定客服的情况下
                    $allocate = UserUtil::allocateWaiter($user->id);
                    if ($allocate['code'] != 200) {
                        // todo 调试
//                        var_dump($allocate['data']);
                        // 没有分配到客服，保存到未读消息队列
                        MessageRedis::saveUnhandleMsg($user->identifier , $user->id , $param);
                        // 通知客户端没有客服
                        UserUtil::noWaiterTip($auth->identifier , $user->id , $group->id);
                    }
                }
                // 初始化消息已读/未读
                GroupMessageReadStatusModel::initByGroupMessageId($group_message_id , $param['group_id'] , $user->id);
                $user_ids = GroupMemberModel::getUserIdByGroupId($param['group_id']);
                // 找到该条消息
                $msg = GroupMessageModel::findById($group_message_id);
                // 处理消息
                MessageUtil::handleGroupMessage($msg);
                DB::commit();
                $auth->sendAll($user_ids , 'group_message' , $msg);
                if (isset($msg_with_no_waiter)) {
                    // 没有客服
                    $auth->pushAll($user_ids , 'group_message' , $msg_with_no_waiter);
                }
                $auth->pushAll($user_ids , 'refresh_unread_message');
                return self::success($msg);
            } catch(Exception $e) {
                DB::rollBack();
                return self::error((new Throwable())->exceptionJsonHandlerInDev($e , true) , 500);
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
            $group_message_id = GroupMessageModel::u_insertGetId($param['user_id'] , $param['group_id'] , $type , $param['message'] , $param['extra']);
            $msg = GroupMessageModel::findById($group_message_id);
            MessageUtil::handleGroupMessage($msg);
            GroupMessageReadStatusModel::initByGroupMessageId($group_message_id , $param['group_id'] , $user->id);
            $user_ids = GroupMemberModel::getUserIdByGroupId($param['group_id']);
            DB::commit();
            $auth->sendAll($user_ids , 'group_message' , $msg);
            $auth->pushAll($user_ids , 'refresh_unread_message');
            return self::success($msg);
        } catch(Exception $e) {
            DB::rollBack();
            return self::error((new Throwable())->exceptionJsonHandlerInDev($e , true) , 500);
        }
    }

    /**
     * 私聊消息发送
     *
     * @throws \Exception
     */
    public static function send(Auth $auth , $type , array $param = [])
    {
        $param['user_id']   = $auth->user->id;
        $param['type']      = $type;
        return ChatUtil::send($auth , $param);

    }

    /**
     * 群消息发送
     *
     * @throws \Exception
     */
    public static function groupSend(Auth $auth , $type , array $param = [])
    {
        $param['user_id']   = $auth->user->id;
        $param['type']      = $type;
//        print_r($param);
        return ChatUtil::groupSend($auth , $param);
    }
}