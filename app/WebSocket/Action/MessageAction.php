<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 21:34
 */

namespace App\WebSocket\Action;


use App\Model\DeleteMessageModel;
use App\Model\FriendModel;
use App\Model\GroupMemberModel;
use App\Model\GroupMessageModel;
use App\Model\GroupModel;
use App\Model\MessageModel;
use App\Model\MessageReadStatusModel;
use App\Model\UserModel;
use App\Util\ChatUtil;
use App\WebSocket\Auth;
use App\WebSocket\Util\MessageUtil;
use App\WebSocket\Util\UserUtil;
use function core\array_unit;
use Core\Lib\Validator;
use Exception;
use Illuminate\Support\Facades\DB;


class MessageAction extends Action
{
    // 历史记录
    public static function history(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'friend_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $limit_id  = empty($param['limit_id']) ? 0 : $param['limit_id'];
        $limit     = empty($param['limit']) ? config('app.limit') : $param['limit'];
        $chat_id = ChatUtil::chatId($auth->user->id , $param['friend_id']);
        try {
            DB::beginTransaction();
            // 删除阅后即焚消息
            $id_list = MessageModel::getBurnIdsWithFriendReadedByChatId($chat_id);
            MessageUtil::delMessageByIds($id_list);
            $res = MessageModel::history($auth->user->id , $chat_id , $limit_id , $limit);
            foreach ($res as $v)
            {
                MessageUtil::handleMessage($v , $auth->user->id , $param['friend_id']);
            }
            DB::commit();
            return self::success($res);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function resetUnread(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'friend_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $chat_id = ChatUtil::chatId($auth->user->id , $param['friend_id']);
        MessageReadStatusModel::updateReadStatusByUserIdAndChatIdExcludeBurn($auth->user->id , $chat_id , 1);
        // 通知用户刷新会话列表
        $auth->push($auth->user->id , 'refresh_session');
        $auth->push($auth->user->id , 'refresh_unread_count');
        return self::success();
    }

    // 删除消息记录
    public static function delete(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'message_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $message_id = json_decode($param['message_id'] , true);
        if (empty($message_id)) {
            return self::error('请提供待删除的消息');
        }
        try {
            DB::beginTransaction();
            foreach ($message_id as $v)
            {
                $message = MessageModel::findById($v);
                if (empty($message)) {
                    DB::rollBack();
                    return self::error('包含不存在的消息id' , 404);
                }
                // 检查消息是否已经被删除

                $data = [
                    'user_id'   => $auth->user->id ,
                    'type'      => 'private' ,
                    'message_id' => $v ,
                    'target_id'   => $message->chat_id ,
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

    public static function readed(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'message_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $res = MessageReadStatusModel::updateReadStatusByUserIdAndMessageId($auth->user->id , $param['message_id'] , 1);
        if ($res > 0) {
            return self::success();
        }
        return self::error('操作失败');
    }

    // 消息撤回
    public static function withdraw(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'message_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $res = MessageModel::findById($param['message_id']);
        if (empty($res)) {
            return self::error('未找到对应的消息' , 404);
        }
        if ($res->user_id != $auth->user->id) {
            return self::error('您无权限撤回他人消息' , 403);
        }
        $deny_withdraw_message_type = config('business.deny_withdraw_message_type');
        if (in_array($res->type , $deny_withdraw_message_type)) {
            return self::error('该消息类型不支持撤回' , 403);
        }
        $withdraw_duration = config('app.withdraw_duration');
        if ($withdraw_duration < time() - strtotime($res->create_time)) {
            return self::error(sprintf('超过%s秒，不允许操作' , $withdraw_duration) , 403);
        }
        $other_id = ChatUtil::otherId($res->chat_id , $res->user_id);
        MessageModel::updateById($param['message_id'] , [
            'type' => 'withdraw' ,
            'message' => sprintf('"%s" 撤回了消息' , $res->user->nickname) ,
        ]);
        $res = MessageModel::findById($param['message_id']);
        MessageUtil::handleMessage($res , $res->user_id , $other_id);
        $user_ids = ChatUtil::userIds($res->chat_id);
        // 刷新会话
        $auth->pushAll($user_ids , 'refresh_session');
        $auth->sendAll($user_ids , 'refresh_private_message' , $res);
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
                $msg = MessageModel::findById($v);
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
                $msg = MessageModel::findById($v);
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
                $msg = MessageModel::findById($v);
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
                $msg = MessageModel::findById($v);
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