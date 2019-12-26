<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 21:34
 */

namespace App\WebSocket\Action;


use App\Data\MessageReadStatusData;
use App\Model\DeleteMessageForPrivateModel;
use App\Model\DeleteMessageModel;
use App\Model\FriendModel;
use App\Model\GroupMemberModel;
use App\Model\GroupMessageModel;
use App\Model\GroupModel;
use App\Model\MessageModel;
use App\Model\MessageReadStatusModel;
use App\Model\UserModel;
use App\Util\AesUtil;
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

    //
    public static function lastest(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'friend_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $limit_id  = empty($param['limit_id']) ? 0 : $param['limit_id'];
        $chat_id = ChatUtil::chatId($auth->user->id , $param['friend_id']);
        try {
            DB::beginTransaction();
            // 删除阅后即焚消息
            $id_list = MessageModel::getBurnIdsWithFriendReadedByChatId($chat_id);
            MessageUtil::delMessageByIds($id_list);
            $res = MessageModel::lastest($auth->user->id , $chat_id , $limit_id);
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
        try {
            $res = MessageReadStatusModel::unreadByUserIdAndChatIdExcludeBurnAndVoice($auth->user->id , $chat_id);
            foreach ($res as $v)
            {
                if (!empty(MessageReadStatusModel::findByUserIdAndMessageId($auth->user->id , $v->id))) {
                    continue ;
                }
                MessageReadStatusData::insertGetId($auth->identifier , $auth->user->id , $v->chat_id , $v->id , 1);
            }
            // 通知用户刷新会话列表
            $auth->push($auth->user->id , 'refresh_session');
            $auth->push($auth->user->id , 'refresh_unread_count');
            $auth->push($auth->user->id , 'refresh_session_unread_count');
            return self::success();
        } catch(Exception $e) {
            throw $e;
        }
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
                DeleteMessageForPrivateModel::u_insertGetId($auth->identifier , $auth->user->id , $v , $message->chat_id);
            }
            DB::commit();
            $auth->push($auth->user->id , 'refresh_session');
            $auth->push($auth->user->id , 'refresh_unread_count');
            $auth->push($auth->user->id , 'refresh_session_unread_count');
            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function readedForBurn(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'message_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $message = MessageModel::findById($param['message_id']);
        if (empty($message)) {
            return self::error('未找到消息id对应的记录' , 404);
        }
        if ($message->flag != 'burn') {
            return self::error('并非阅后即焚消息' , 403);
        }
        $user_ids = ChatUtil::userIds($message->chat_id);
        if (!in_array($auth->user->id , $user_ids)) {
            return self::error('你无法更改他人的消息读取状态' , 403);
        }
        $res = MessageReadStatusModel::findByUserIdAndMessageId($auth->user->id , $param['message_id']);
        if (!empty($res)) {
            return self::error('操作失败！该条消息已经是已读状态');
        }
        MessageReadStatusData::insertGetId($auth->identifier , $auth->user->id , $message->chat_id , $message->id , 1);
        // 推送给该条消息的双方，将本地数据库的消息删除
        $auth->pushAll($user_ids , 'delete_private_message_from_cache' , [$param['message_id']]);
        $auth->push($auth->user->id , 'refresh_session');
        $auth->push($auth->user->id , 'refresh_unread_count');
        $auth->push($auth->user->id , 'refresh_session_unread_count');
        return self::success();
    }

    // 设置单条消息为已读
    public static function readed(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'message_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $message = MessageModel::findById($param['message_id']);
        if (empty($message)) {
            return self::error('未找到消息id对应的记录' , 404);
        }
        $user_ids = ChatUtil::userIds($message->chat_id);
        if (!in_array($auth->user->id , $user_ids)) {
            return self::error('你无法更改他人的消息读取状态' , 403);
        }
        $res = MessageReadStatusModel::findByUserIdAndMessageId($auth->user->id , $param['message_id']);
        if (!empty($res)) {
            return self::error('操作失败！该条消息已经是已读状态');
        }
        MessageReadStatusData::insertGetId($auth->identifier , $auth->user->id , $message->chat_id , $message->id , 1);
        $sender = ChatUtil::otherId($message->chat_id , $message->user_id);
        // 推送给该条消息的双方，将本地数据库的消息删除
        $auth->push($sender , 'readed_for_private' , [$param['message_id']]);
        $auth->push($auth->user->id , 'refresh_session');
        $auth->push($auth->user->id , 'refresh_unread_count');
        $auth->push($auth->user->id , 'refresh_session_unread_count');
        return self::success();
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
        $message = sprintf('"%s" 撤回了消息' , $res->user->nickname);
        MessageModel::updateById($param['message_id'] , [
            'type' => 'withdraw' ,
            'message' => $res->old == 1 ?
                $message :
                AesUtil::encrypt($message , $res->aes_key , config('app.aes_vi')) ,
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
                    'other_id' => $friend->id ,
                    'type' => $v->type ,
                    'message' => $v->message ,
                    'extra' => $v->extra ,
                    'old'   => $v->old ,
                    'aes_key' => $v->aes_key ,
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
                    'aes_key' => $v->aes_key ,
                    'old' => $v->old ,
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
            $message = json_encode($message_id);
            $old = 1;
            if (config('app.enable_encrypt')) {
                $old = 0;
                $message =  AesUtil::encrypt($message , $auth->user->aes_key , config('app.aes_vi'));
            }
            $res = ChatUtil::send($auth , [
                'user_id' => $auth->user->id ,
                'other_id' => $friend->id ,
                'type' => 'message_set' ,
                'message' => $message ,
                'extra' => 'private' ,
                'old' => $old ,
            ] , true);
            if ($res['code'] != 200) {
                return self::error('合并转发失败：' . $res['data'] , $res['code']);
            }
            return self::success();
            // 转发到私聊群
        }

        if ($param['type'] == 'group') {
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
            $message = json_encode($message_id);
            $old = 1;
            if (config('app.enable_encrypt')) {
                $old = 0;
                $message =  AesUtil::encrypt($message , $auth->user->aes_key , config('app.aes_vi'));
            }
            $res = ChatUtil::groupSend($auth , [
                'user_id' => $auth->user->id ,
                'group_id' => $group->id ,
                'type' => 'message_set' ,
                'message' => $message ,
                'extra' => 'private' ,
                'old' => $old
            ] , true);
            if ($res['code'] != 200) {
                return self::error('合并转发失败：' . $res['data'] , $res['code']);
            }
            return self::success();
        }

        return self::error('不支持的 type，当前受支持的 type 有 ' . implode(',' , $type_range));
    }

    public static function sync(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'id_list' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $id_list = json_decode($param['id_list'] , true);
        $res = MessageModel::getByUserIdAndIdsExcludeDeleted($auth->user->id , $id_list);
//        $res = MessageModel::getByIds($id_list);
        foreach ($res as $v)
        {
            $other_id = ChatUtil::otherId($v->chat_id , $auth->user->id);
            MessageUtil::handleMessage($v , $auth->user->id , $other_id);
        }
        return self::success($res);
    }

    public static function syncForSingle(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'message_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $res = MessageModel::findByUserIdAndIdExcludeDeleted($auth->user->id , $param['message_id']);
        if (!empty($res)) {
            $other_id = ChatUtil::otherId($res->chat_id , $auth->user->id);
            MessageUtil::handleMessage($res , $auth->user->id , $other_id);
        }
        return self::success($res);
    }
}