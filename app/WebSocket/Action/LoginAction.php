<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 9:39
 */

namespace App\WebSocket\Action;


use App\Model\Group;
use App\Model\GroupMember;
use App\Model\GroupMessage;
use App\Model\GroupMessageReadStatus;
use App\Model\User;
use App\Model\UserToken;
use App\Redis\MessageRedis;
use App\Redis\UserRedis;
use App\Util\Misc;
use App\Util\Push;
use function core\array_unit;
use Core\Lib\Validator;
use function core\ssl_random;

use App\WebSocket\Base;
use Exception;
use Illuminate\Support\Facades\DB;

class LoginAction extends Action
{
    public static function login(Base $app , array $param)
    {
        $validator = Validator::make($param , [
            'unique_code'    => 'required' ,
        ] , [
            'unique_code.required' => '必须' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->error());
        }
        $user = User::findByUniqueCode($param['unique_code']);
        if (empty($user)) {
            return self::error([
                'unique_code' => '未找到当前提供的 unique_code 对应的用户' ,
            ]);
        }
        // 登录成功
        $param['identifier'] = $user->identifier;
        $param['user_id'] = $user->id;
        $param['token']  = token();
        $param['expire'] = date('Y-m-d H:i:s' , time() + config('app.timeout'));
        UserToken::insert(array_unit($param , [
            'token' ,
            'expire' ,
            'user_id' ,
            'identifier' ,
        ]));
        // 绑定 user_id <=> fd
        UserRedis::fdByUserId($app->identifier , $user->id , $app->fd);
        UserRedis::fdMappingUserId($app->identifier , $app->fd , $user->id);
        // 登录成功后消费未读游客消息（平台咨询）队列
        self::util_allocate($app , $user);
        // 初始化咨询通道
        UserAction::util_initAdvoiseGroup($user->id);
        // 推送一条未读消息数量
        return self::success($param['token']);
    }

    // 自动分配客服
    public static function util_allocate(Base $app , User $user)
    {
        if (empty($user)) {
            return ;
        }
        if ($user->role != 'admin') {
            // 不是工作人员，分配在线客服
            return ;
        }
        try {
            DB::beginTransaction();
            $group_msg = MessageRedis::consumeUnhandleMsg($app->identifier);
            $push = [];
            foreach ($group_msg as $v)
            {
                if (empty(GroupMember::findByUserIdAndGroupId($user->id , $v['group_id']))) {
                    GroupMember::insert([
                        'user_id' => $user->id ,
                        'group_id' => $v['group_id']
                    ]);
                    $user_ids = GroupMember::getUserIdByGroupId($v['group_id']);
                    $app->pushAll($user_ids , 'refresh_session');
                }
                $user_ids = GroupMember::getUserIdByGroupId($v['group_id']);
                $group_message_id = GroupMessage::insertGetId([
                    'user_id' => $user->id ,
                    'group_id' => $v['group_id'] ,
                    'type' => 'text' ,
                    'message' => sprintf('系统通知：您好，客服 【%s】 很高兴为您服务' , $user->username) ,
                ]);
                foreach ($user_ids as $v1)
                {
                    $is_read = $v1 == $user->id ? 'y' : 'n';
                    GroupMessageReadStatus::insert([
                        'user_id' => $user->id ,
                        'group_message_id' => $group_message_id ,
                        'is_read' => $is_read
                    ]);
                }
                $group = Group::findById($v['group_id']);
                $msg = GroupMessage::findById($group_message_id);
                $msg->session_id = Misc::sessionId('group' , $v['group_id']);
                if ($group->is_service == 'y' && $msg->user->role == 'admin') {
                    $msg->user->username = '客服 ' . $msg->user->username;
                    $msg->user->nickname = '客服 ' . $msg->user->nickname;
                }
                $push[] = [
                    'identifier' => $user->identifier ,
                    'user_ids' => $user_ids ,
                    'type' => 'group_message' ,
                    'data' => $msg
                ];
                // 绑定活跃群组
                UserRedis::groupBindWaiter($app->identifier , $v['group_id'] , $user->id);
                UserRedis::delNoWaiterForGroup($app->identifier , $v['group_id']);
            }
            DB::commit();
            foreach ($push as $v)
            {
                Push::multiple($v['identifier'] , $v['user_ids'] , $v['type'] , $v['data']);
            }
        } catch (Exception $e) {
            DB::rollBack();
            $app->push($user->id , 'error' , $e);
        }
    }
}