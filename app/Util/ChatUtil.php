<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/11
 * Time: 11:19
 */

namespace App\Util;


use App\Model\BlacklistModel;
use App\Model\DeleteMessageModel;
use App\Model\FriendModel;
use App\Model\GroupMemberModel;
use App\Model\GroupMessageModel;
use App\Model\GroupMessageReadStatusModel;
use App\Model\GroupModel;
use App\Model\GroupNoticeModel;
use App\Model\MessageModel;
use App\Model\MessageReadStatusModel;
use App\Model\ProgramErrorLogModel;
use App\Model\UserModel;
use App\Model\UserOptionModel;
use App\Redis\MessageRedis;
use App\Redis\UserRedis;
use App\WebSocket\Base;
use App\WebSocket\Util\MessageUtil;
use function core\array_unit;
use Core\Lib\Throwable;
use Core\Lib\Validator;
use Exception;
use Illuminate\Support\Facades\DB;

use App\WebSocket\Util\UserUtil as UserUtilWebSocket;


class ChatUtil extends Util
{
    /**
     * 生成会话id
     *
     * @param int $sender 发送者id
     * @param int $receiver 接收者id
     * @return string
     */
    public static function chatId(int $sender , int $receiver): string
    {
        $min = min($sender , $receiver);
        $max = max($sender , $receiver);
        return sprintf('%d_%d' , $min , $max);
    }

    /**
     * 私聊消息发送（这边是去掉了各种用户认证）
     * @throws Exception
     */
    public static function send(Base $base , array $param , bool $push_all = false)
    {
        $validator = Validator::make($param , [
            'user_id' => 'required' ,
            'friend_id' => 'required' ,
            'type' => 'required' ,
            'message' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $type_range = config('business.message_type');
        if (!in_array($param['type'] , $type_range)) {
            return self::error('不支持的消息类型，当前受支持的消息类型有：' . implode(' , ' , $type_range) , 401);
        }
        // 检查是否在群里面
        // 检查是否时好友
        $relation = FriendModel::findByUserIdAndFriendId($param['user_id'] , $param['friend_id']);
        if (empty($relation)) {
//            return self::error('你们还不是好友，禁止操作' , 403);
        }
        $user = UserModel::findById($param['user_id']);
        if (empty($user)) {
            return self::error('未找到用户' , 404);
        }
        // 该条消息是否是阅后即焚的消息
        $param['flag'] = empty($relation) ? 'normal' :
            ($relation->burn == 1 ? 'burn' : 'normal');
        $param['chat_id'] = ChatUtil::chatId($param['user_id'] , $param['friend_id']);
        $param['extra'] = $param['extra'] ?? '';
        // 这边做基本的认证
        $blocked = BlacklistModel::blocked($param['friend_id'] , $param['user_id']);
        $param['blocked'] = (int) $blocked;
        $param['old'] = empty($param['old']) ? 0 : $param['old'];
        $param['aes_key'] = $user->aes_key;
        try {
            DB::beginTransaction();
            $id = MessageModel::insertGetId(array_unit($param , [
                'user_id' ,
                'chat_id' ,
                'message' ,
                'type' ,
                'flag' ,
                'extra' ,
                'blocked' ,
                'aes_key' ,
                'old' ,
            ]));
            MessageReadStatusModel::initByMessageId($id , $param['chat_id'] , $param['user_id'] , $param['friend_id']);
            if ($blocked) {
                // 接收方把发送方拉黑了
                DeleteMessageModel::insertGetId([
                    'user_id'   => $param['friend_id'] ,
                    'type'      => 'private' ,
                    'message_id' => $id ,
                    'target_id' => $param['chat_id'] ,
                ]);
            }
            $msg = MessageModel::findById($id);
            SessionUtil::createOrUpdate($param['user_id'] , 'private' , $param['chat_id']);
            SessionUtil::createOrUpdate($param['friend_id'] , 'private' , $param['chat_id']);
            MessageUtil::handleMessage($msg , $param['user_id'] , $param['friend_id']);
            DB::commit();
            if (!$blocked) {
                $user_ids = [$param['user_id'] , $param['friend_id']];
//                var_dump(json_encode($user_ids));
//                var_dump('当前登录用户【user_id: ' . UserRedis::fdMappingUserId($base->identifier , $base->fd) . '】的 fd' . $base->fd);
//                foreach ($user_ids as $v)
//                {
//                    $fd = UserRedis::userIdMappingFd($base->identifier , $v);
//                    var_dump('这边推送的用户id【' . $v . '】对应的 fd' . json_encode($fd));
//                }
                if ($push_all) {
                    // 用于消息转发
                    $base->pushAll($user_ids , 'private_message' , $msg);
                } else {
                    // 用于正常聊天
                    $base->sendAll($user_ids , 'private_message' , $msg);
                }
                $base->pushAll($user_ids , 'refresh_session');
                $base->pushAll($user_ids , 'refresh_unread_count');
                $base->pushAll($user_ids , 'refresh_session_unread_count');
                AppPushUtil::pushCheckForFriend($base->platform , $param['user_id'] , $param['friend_id'] , function() use($param , $msg){
                    $message = $param['old'] == 1 ? $msg->message : AesUtil::decrypt($msg->message , $param['aes_key'] , config('app.aes_vi'));
                    $res = AppPushUtil::pushForPrivate($param['friend_id'] , $message , '你收到了一条好友消息' , $msg);
                    if ($res['code'] != 200) {
                        ProgramErrorLogModel::u_insertGetId("Notice: App推送失败 [chat_id: {$param['chat_id']}] [sender: {$param['user_id']}; receiver: {$param['friend_id']}]");
                    }
                });
                AppPushUtil::pushCheckWithNewForFriend($param['user_id'] , $param['friend_id'] , function() use($param , $msg , $base){
                    $base->push($param['friend_id'] , 'new');
                });
            } else {
                if ($push_all) {
                    // 用于消息转发
                    $base->push($param['user_id'] , 'private_message' , $msg);
                }
            }
            return self::success($msg);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 群聊消息发送
     */
    public static function groupSend(Base $base , array $param , bool $push_all = false)
    {
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
            'type' => 'required' ,
            'user_id' => 'required' ,
            'message' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $type_range = config('business.message_type');
        if (!in_array($param['type'] , $type_range)) {
            return self::error('不支持的消息类型，当前受支持的消息类型有：' . implode(' , ' , $type_range) , 401);
        }
        // 检查群是否还存在
        $group = GroupModel::findById($param['group_id']);
        if (empty($group)) {
            return self::error('群不存在！' , 404);
        }
        // 检查群是否设置了全体禁言
        if ($group->banned == 1) {
            return self::error('群主已开启禁言' , 403);
        }
        $member = GroupMemberModel::findByUserIdAndGroupId($param['user_id'] , $param['group_id']);
        if (empty($member)) {
            return self::error('您不在该群，禁止操作' , 403);
        }
        if ($member->banned == 1) {
            // 被设置禁言
            return self::error('您已经被管理员设置为禁言');
        }
        $user = UserModel::findById($param['user_id']);
        if (empty($user)) {
            return self::error('未找到用户' , 404);
        }
        $group_target_user = config('business.group_target_user');
        $param['extra'] = $param['extra'] ?? '';
        $param['target_user']       = $param['target_user'] ?? '';
        $param['target_User_ids']   = $param['target_user_ids'] ?? '';
        // 如果用户没有指定推送的人，那么群推送
        $param['target_user'] = in_array($param['target_user'] ,  $group_target_user) ? $param['target_user'] : 'auto';
        $param['old'] = empty($param['old']) ? 0 : $param['old'];
        $param['aes_key'] = $user->key;
        $param['message'] = AesUtil::encrypt($param['message'] , $user->aes_key , config('app.aes_vi'));
        try {
            DB::beginTransaction();
            $group_message_id = GroupMessageModel::insertGetId(array_unit($param , [
                'user_id' ,
                'group_id' ,
                'message' ,
                'type' ,
                'extra' ,
                'aes_key' ,
                'old' ,
            ]));
            GroupMessageReadStatusModel::initByGroupMessageId($group_message_id , $param['group_id'] , $param['user_id']);
            $msg = GroupMessageModel::findById($group_message_id);
            MessageUtil::handleGroupMessage($msg);
            $user_ids = GroupMemberModel::getUserIdByGroupId($param['group_id']);
            foreach ($user_ids as $v)
            {
                SessionUtil::createOrUpdate($v , 'group' , $param['group_id']);
            }
            DB::commit();
            if ($push_all) {
                $base->pushAll($user_ids , 'group_message' , $msg);
            } else {
                $base->sendAll($user_ids , 'group_message' , $msg);
            }
            $base->pushAll($user_ids , 'refresh_session');
            $base->pushAll($user_ids , 'refresh_unread_count');
            $base->pushAll($user_ids , 'refresh_session_unread_count');
            if ($param['target_user'] == 'designation') {
                $target_user_ids = json_decode($param['target_user_ids'] , true);
                if (!empty($target_user_ids)) {
                    // 用户手动指定推送的用户
                    $user_ids = array_intersect($target_user_ids , $user_ids);
                }
            }
            foreach ($user_ids as $v)
            {
                if ($v == $param['user_id']) {
                    // 跳过发送消息的人
                    continue ;
                }
                AppPushUtil::pushCheckForGroup($base->platform , $param['user_id'] , $group->id , function() use($v , $param , $msg){
                    $message = $param['old'] == 1 ? $msg->message : AesUtil::decrypt($msg->message , $param['aes_key'] , config('app.aes_vi'));
                    $res = AppPushUtil::pushForGroup($v , $message , '你收到了一条群消息' , $msg);
                    if ($res['code'] != 200) {
                        ProgramErrorLogModel::u_insertGetId("Notice: App推送失败 [group_id: {$param['group_id']}] [sender: {$param['user_id']}; receiver: {$v}]");
                    }
                });
                AppPushUtil::pushCheckWithNewForGroup($param['user_id'] , $group->id , function() use($v , $param , $base){
                    $base->push($v , 'new');
                });
            }
            return self::success($msg);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function otherId(string $chat_id , int $user_id)
    {
        if (empty($chat_id)) {
            return 0;
        }
        $res = explode('_' , $chat_id);
        foreach ($res as $v)
        {
            if ($v == $user_id) {
                continue ;
            }
            return $v;
        }
    }

    // 获取好友双方
    public static function userIds(string $chat_id)
    {
        return explode('_' , $chat_id);
    }

    // 会话ID（群聊|私聊）
    public static function sessionId(string $type = '' , $id = 0)
    {
        return md5(sprintf('%s_%s' , $type , $id));
    }


    /**
     * 平台咨询
     *
     * @param Base $auth
     * @param array $param
     * @return array
     */
    public static function advoise(Base $base , array $param)
    {
        $validator = Validator::make($param , [
            'group_id'  => 'required' ,
            'type'      => 'required' ,
            'user_id'   => 'required' ,
            'message'   => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $type_range = config('business.message_type');
        if (!in_array($param['type'] , $type_range)) {
            return self::error('不支持的消息类型，当前受支持的消息类型有：' . implode(' , ' , $type_range) , 401);
        }
        // 检查群是否还存在
        $group = GroupModel::findById($param['group_id']);
        if (empty($group)) {
            return self::error('群不存在！' , 404);
        }
        // 检查群是否是客服群
        if ($group->is_service == 0) {
            return self::error('不是客服群，禁止操作' , 403);
        }
        $user = UserModel::findById($param['user_id']);
        if (empty($user)) {
            return self::error('用户不存在！' , 404);
        }
        // 检查是否在群里面
        $exist = GroupMemberModel::exist($param['user_id'] , $param['group_id']);
        if (!$exist) {
            return self::error('您不在该群，禁止操作' , 403);
        }
        $param['extra'] = $param['extra'] ?? '';
        // 检查当前登录类型
        if ($user->role == 'user') {
            try {
                DB::beginTransaction();
                $group_message_id = GroupMessageModel::u_insertGetId($param['user_id'] , $param['group_id'] , $param['type'] , $param['message'] , $param['extra']);
                $bind_waiter = UserRedis::groupBindWaiter($user->identifier , $group->id);
                if (empty($bind_waiter)) {
                    // 没有绑定客服的情况下
                    $allocate = UserUtilWebSocket::allocateWaiter($user->id , $group->id);
                    if ($allocate['code'] != 200) {
//                        var_dump($allocate['data']);
                        // 没有分配到客服，保存到未读消息队列
                        MessageRedis::saveUnhandleMsg($base->identifier , $user->id , $param);
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
                $base->sendAll($user_ids , 'group_message' , $msg);
                if ($allocate['code'] != 200) {
                    // 通知客户端没有客服
                    UserUtilWebSocket::noWaiterTip($base->identifier , $user->id , $group->id);
                }
                $base->pushAll($user_ids , 'refresh_unread_count');
                $base->pushAll($user_ids , 'refresh_session_unread_count');
                foreach ($user_ids as $v)
                {
                    if ($v == $param['user_id']) {
                        // 跳过发送消息的人
                        continue ;
                    }
                    AppPushUtil::pushCheckForGroup($base->platform , $param['user_id'] , $group->id , function() use($v , $param , $msg){
                        $res = AppPushUtil::pushForGroup($v , $msg->message , '你收到了一条群消息' , $msg);
                        if ($res['code'] != 200) {
                            ProgramErrorLogModel::u_insertGetId("Notice: App推送失败 [group_id: {$param['group_id']}] [sender: {$param['user_id']}; receiver: {$v}]");
                        }
                    });
                    AppPushUtil::pushCheckWithNewForGroup($param['user_id'] , $group->id , function() use($v , $param , $base){
                        $base->push($v , 'new');
                    });

                }
                return self::success($msg);
            } catch(Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }
        try {
            DB::beginTransaction();
            // 检查当前群组是否绑定当前用户
            $waiter = UserRedis::groupBindWaiter($base->identifier , $param['group_id']);
            if ($waiter != $user->id) {
                DB::rollBack();
                // 当前群的活跃客服并非您的情况下
                return self::error('您并非当前咨询通道的活跃客服！' , 403);
            }
            // 工作人员回复
            $group_message_id = GroupMessageModel::u_insertGetId($param['user_id'] , $param['group_id'] , $param['type'] , $param['message'] , $param['extra']);
            $msg = GroupMessageModel::findById($group_message_id);
            MessageUtil::handleGroupMessage($msg);
            GroupMessageReadStatusModel::initByGroupMessageId($group_message_id , $param['group_id'] , $user->id);
            $user_ids = GroupMemberModel::getUserIdByGroupId($param['group_id']);
            DB::commit();
            $base->sendAll($user_ids , 'group_message' , $msg);
            $base->pushAll($user_ids , 'refresh_unread_count');
            $base->pushAll($user_ids , 'refresh_session_unread_count');
            foreach ($user_ids as $v)
            {
                if ($v == $param['user_id']) {
                    // 跳过发送消息的人
                    continue ;
                }
                AppPushUtil::pushCheckForGroup($base->platform , $param['user_id'] , $group->id , function() use($v , $param , $msg){
                    $res = AppPushUtil::pushForGroup($v , $msg->message , '你收到了一条群消息' , $msg);
                    if ($res['code'] != 200) {
                        ProgramErrorLogModel::u_insertGetId("Notice: App推送失败 [group_id: {$param['group_id']}] [sender: {$param['user_id']}; receiver: {$v}]");
                    }
                });
                AppPushUtil::pushCheckWithNewForGroup($param['user_id'] , $group->id , function() use($v , $param , $base){
                    $base->push($v , 'new');
                });
            }
            return self::success($msg);
        } catch(Exception $e) {
            DB::rollBack();
            return self::error((new Throwable())->exceptionJsonHandlerInDev($e , true) , 500);
        }
    }
}