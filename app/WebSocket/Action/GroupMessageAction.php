<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/20
 * Time: 10:52
 */

namespace App\WebSocket\Action;

use App\Model\DeleteMessageModel;
use App\Model\FriendModel;
use App\Model\GroupModel;
use App\Model\GroupMemberModel;
use App\Model\GroupMessageModel;
use App\Model\GroupMessageReadStatusModel;
use App\Model\UserModel;
use App\Util\ChatUtil;
use App\Util\MiscUtil;
use App\WebSocket\Auth;
use function core\array_unit;
use Core\Lib\Validator;
use App\WebSocket\Util\MessageUtil;


use function core\convert_obj;
use function core\obj_to_array;
use Exception;
use Illuminate\Support\Facades\DB;



class GroupMessageAction extends Action
{

    public static function history(Auth $auth , array $param)
    {
        // 获取群聊数据
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $group = GroupModel::findById($param['group_id']);
        if (empty($group)) {
            return self::error('未找到群对应信息' , 404);
        }
        $limit_id = empty($param['limit_id']) ? 0 : $param['limit_id'];
        $limit = empty($param['limit']) ? 0 : $param['limit'];
        $res = GroupMessageModel::history($auth->user->id , $group->id , $limit_id , $limit);
        foreach ($res as $v)
        {
            MessageUtil::handleGroupMessage($v);
        }
        return self::success($res);
    }

    // 设置未读消息数量
    public static function resetUnread(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        GroupMessageReadStatusModel::updateStatusByUserIdAndGroupId($auth->user->id , $param['group_id'] , 1);
        // 通知用户刷新会话列表
        $auth->push($auth->user->id , 'refresh_session');
        $auth->push($auth->user->id , 'refresh_unread_count');
        $auth->push($auth->user->id , 'refresh_session_unread_count');
        return self::success();
    }

    // 删除消息记录
    public static function delete(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_message_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $group_message_id = json_decode($param['group_message_id'] , true);
        if (empty($group_message_id)) {
            return self::error('请提供待删除的消息');
        }
        try {
            DB::beginTransaction();
            foreach ($group_message_id as $v)
            {
                $group_message = GroupMessageModel::findById($v);
                if (empty($group_message)) {
                    DB::rollBack();
                    return self::error('包含不存在的消息' , 404);
                }
                $data = [
                    'user_id'   => $auth->user->id ,
                    'type'      => 'group' ,
                    'message_id' => $v ,
                    'target_id' => $group_message->group_id ,
                ];
                DeleteMessageModel::insert($data);
            }
            DB::commit();
            $auth->push($auth->user->id , 'refresh_session');
            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // 消息撤回
    public static function withdraw(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_message_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $res = GroupMessageModel::findById($param['group_message_id']);
        if (empty($res)) {
            return self::error('未找到对应的消息' , 404);
        }
        $deny_withdraw_message_type = config('business.deny_withdraw_message_type');
        if (in_array($res->type , $deny_withdraw_message_type)) {
            return self::error('该消息类型不支持撤回' , 403);
        }
        if ($res->user_id != $auth->user->id) {
            return self::error('您无权限撤回他人消息' , 403);
        }
        $withdraw_duration = config('app.withdraw_duration');
        if ($withdraw_duration < time() - strtotime($res->create_time)) {
            return self::error(sprintf('超过%s秒，不允许操作' , $withdraw_duration) , 403);
        }
        GroupMessageModel::updateById($param['group_message_id'] , [
            'type' => 'withdraw' ,
            'message' => sprintf('"%s" 撤回了消息' , $res->user->nickname) ,
        ]);
        $res = GroupMessageModel::findById($param['group_message_id']);
        MessageUtil::handleGroupMessage($res);
        $user_ids = GroupMemberModel::getUserIdByGroupId($res->group_id);
        $auth->pushAll($user_ids , 'refresh_session');
        $auth->sendAll($user_ids , 'refresh_group_message' , $res);
        return self::success($res);
    }


    // 逐条转发
    public static function serialForward(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'type'       => 'required' ,
            'message_id' => 'required' ,
            'target_id'  => 'required'
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $type_range = config('business.forward_type');
        if (!in_array($param['type'] , $type_range)) {
            return self::error('不支持的转发类型，当前受支持的转发类型：' . implode(',' , $type_range));
        }
        if ($param['type'] == 'private') {
            // 转发到私聊群里面
            $friend = UserModel::findById($param['target_id']);
            if (empty($friend)) {
                return self::error('未找到用户信息' , 404);
            }
            if (!FriendModel::isFriend($auth->user->id , $friend->id)) {
                return self::error('你们还不是好友' , 403);
            }
            $message_id = json_decode($param['message_id'] , true);
            if (empty($message_id)) {
                return self::error('请选择要转发的消息');
            }
            $deny_forward_message_type = config('business.deny_forward_message_type');
            $msgs = [];
            foreach ($message_id as $v)
            {
                $msg = GroupMessageModel::findById($v);
                if (in_array($msg->type , $deny_forward_message_type)) {
                    return self::error('包含不支持转发的消息类型' , 403);
                }
                $msgs[] = $msg;
            }
            $res = [
                // 失败转发数量
                'error'     => [] ,
                // 成功转发数量
                'success'   => 0
            ];
            foreach ($msgs as $v)
            {
                $forward = ChatUtil::send($auth , [
                    'user_id' => $auth->user->id ,
                    'friend_id' => $friend->id ,
                    'type' => $v->type ,
                    'message' => $v->message ,
                    'extra' => $v->extra ,
                ] , true);
                if ($forward['code'] != 200) {
                    $res['error'][] = [
                        'code' => $forward['code'] ,
                        'data' => $forward['data'] ,
                    ];
                    continue ;
                }
                $res['success']++;
            }
            return self::success($res);
            // 转发到私聊群
        } else if ($param['type'] == 'group') {
            // 转发到群聊群
            // 转发到私聊群里面
            $group = GroupModel::findById($param['target_id']);
            if (empty($group)) {
                return self::error('未找到群组信息' , 404);
            }
            if (!GroupMemberModel::exist($auth->user->id , $group->id)) {
                return self::error('您还不在这个群里面' , 403);
            }
            $message_id = json_decode($param['message_id'] , true);
            if (empty($message_id)) {
                return self::error('请选择要转发的消息');
            }
            $deny_forward_message_type = config('business.deny_forward_message_type');
            $msgs = [];
            foreach ($message_id as $v)
            {
                $msg = GroupMessageModel::findById($v);
                if (in_array($msg->type , $deny_forward_message_type)) {
                    return self::error('包含不支持转发的消息类型' , 403);
                }
                $msgs[] = $msg;
            }
            $res = [
                // 失败转发数量
                'error'     => [] ,
                // 成功转发数量
                'success'   => 0
            ];
            foreach ($msgs as $v)
            {
                $forward = ChatUtil::groupSend($auth , [
                    'user_id' => $auth->user->id ,
                    'group_id' => $group->id ,
                    'type' => $v->type ,
                    'message' => $v->message ,
                    'extra' => $v->extra ,
                ] , true);
                if ($forward['code'] != 200) {
                    $res['error'][] = [
                        'code' => $forward['code'] ,
                        'data' => $forward['data'] ,
                    ];
                    continue ;
                }
                $res['success']++;
            }
            return self::success($res);
        } else {
            // 待扩充
        }


    }

    // 消息合并转发
    public static function mergeForward(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'type'       => 'required' ,
            'message_id' => 'required' ,
            'target_id'  => 'required'
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $type_range = config('business.forward_type');
        if (!in_array($param['type'] , $type_range)) {
            return self::error('不支持的转发类型，当前受支持的转发类型：' . implode(',' , $type_range));
        }
        if ($param['type'] == 'private') {
            // 转发到私聊群里面
            $friend = UserModel::findById($param['target_id']);
            if (empty($friend)) {
                return self::error('未找到用户信息' , 404);
            }
            if (!FriendModel::isFriend($auth->user->id , $friend->id)) {
                return self::error('你们还不是好友' , 403);
            }
            $message_id = json_decode($param['message_id'] , true);
            if (empty($message_id)) {
                return self::error('请选择要转发的消息');
            }
            $deny_forward_message_type = config('business.deny_forward_message_type');
            $msgs = [];
            foreach ($message_id as $v)
            {
                $msg = GroupMessageModel::findById($v);
                if (in_array($msg->type , $deny_forward_message_type)) {
                    return self::error('包含不支持转发的消息类型' , 403);
                }
                $msgs[] = $msg;
            }
            $res = ChatUtil::send($auth , [
                'user_id' => $auth->user->id ,
                'friend_id' => $friend->id ,
                'type' => 'message_set' ,
                'message' => json_encode($message_id) ,
                'extra' => '' ,
            ] , true);
            if ($res['code'] != 200) {
                return self::error('合并转发失败：' . $res['data'] , $res['code']);
            }
            return self::success();
            // 转发到私聊群
        } else if ($param['type'] == 'group') {
            // 转发到群聊群
            // 转发到私聊群里面
            $group = GroupModel::findById($param['target_id']);
            if (empty($group)) {
                return self::error('未找到群组信息' , 404);
            }
            if (!GroupMemberModel::exist($auth->user->id , $group->id)) {
                return self::error('您还不在这个群里面' , 403);
            }
            $message_id = json_decode($param['message_id'] , true);
            if (empty($message_id)) {
                return self::error('请选择要转发的消息');
            }
            $deny_forward_message_type = config('business.deny_forward_message_type');
            $msgs = [];
            foreach ($message_id as $v)
            {
                $msg = GroupMessageModel::findById($v);
                if (in_array($msg->type , $deny_forward_message_type)) {
                    return self::error('包含不支持转发的消息类型' , 403);
                }
                $msgs[] = $msg;
            }
            $res = ChatUtil::groupSend($auth , [
                'user_id' => $auth->user->id ,
                'group_id' => $group->id ,
                'type' => 'message_set' ,
                'message' => json_encode($message_id) ,
                'extra' => '' ,
            ] , true);
            if ($res['code'] != 200) {
                return self::error('合并转发失败：' . $res['data'] , $res['code']);
            }
            return self::success();
        } else {
            // 待扩充
        }
    }

}