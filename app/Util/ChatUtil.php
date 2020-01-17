<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/11
 * Time: 11:19
 */

namespace App\Util;


use App\Cache\MessageReadStatusCache;
use App\Data\FriendData;
use App\Data\GroupMemberData;
use App\Data\GroupMessageReadStatusData;
use App\Data\MessageReadStatusData;
use App\Data\UserData;
use App\Model\BlacklistModel;
use App\Model\DeleteMessageForPrivateModel;
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
use App\Redis\QueueRedis;
use App\Redis\UserRedis;
use App\WebSocket\Base;
use App\WebSocket\Util\MessageUtil;
use function core\array_unit;
use function core\convert_obj;
use Core\Lib\Throwable;
use Core\Lib\Validator;
use function core\random;
use Engine\Facade\WebSocket;
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
        $s_time = microtime(true);
        $validator = Validator::make($param , [
            'user_id' => 'required' ,
            'other_id' => 'required' ,
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
        $relation = FriendModel::findByUserIdAndFriendId($param['user_id'] , $param['other_id']);
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
        $param['chat_id'] = ChatUtil::chatId($param['user_id'] , $param['other_id']);
        $param['extra'] = $param['extra'] ?? '';
        // 这边做基本的认证
        $blocked = BlacklistModel::blocked($param['other_id'] , $param['user_id']);
        $param['blocked'] = (int) $blocked;
        $param['old'] = $param['old'] ?? '';
        $param['old'] = $param['old'] === '' ? 1 : $param['old'];
        $param['aes_key'] = $param['aes_key'] ?? $user->aes_key;
        $param['identifier'] = $base->identifier;
        $param['create_time'] = $param['create_time'] ?? '';
        $param['create_time'] = empty($param['create_time']) ? date('Y-m-d H:i:s') : $param['create_time'];

        if ($param['type'] == 'voice_call') {
            $other = UserData::findByIdentifierAndId($base->identifier , $param['other_id']);
            if ($other->is_system == 1) {
                return self::error('禁止向客服发起语音通话' , 403);
            }
            // 检查接收方是否是客服
            $time = time();
            $datetime = date('Y-m-d H:i:s' , $time);
            // 如果是语音通话
            $param['extra'] = json_encode([
                // 频道
                'channel' => random(64 , 'letter' , true) ,
                // 接听状态
                'status' => 'wait' ,
                // 开始时间
                'start_time' => $datetime ,
                // 结束时间
                'end_time' => $datetime ,
                // 挂断时间
                'close_time' => $datetime ,
                // 开始时间[unix]
                'start_time_for_unix' => $time ,
                // 结束时间[unix]
                'end_time_for_unix' => $time ,
                // 挂断时间[unix]
                'close_time_for_unix' => $time ,
                // 通话时长，单位 s
                'duration' => 0
            ]);
        }
        // 将 html 标签转义成 html 实体
        if ($param['old'] < 1) {
            $aes_vi = config('app.aes_vi');
            $message = AesUtil::decrypt($param['message'] , $param['aes_key']  , $aes_vi);
            $message = strip_tags($message);
            // 重新加密
            $param['message'] = AesUtil::encrypt($message , $param['aes_key'] , $aes_vi);
        } else {
            $param['message'] = strip_tags($param['message']);
        }
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
                'identifier' ,
                'create_time' ,
            ]));
            // 消息已读未读：仅已读的用户记录到数据库；然后未读取的用户不记录数据库；
            MessageReadStatusData::insertGetId($base->identifier , $param['user_id'] , $param['chat_id'] , $id , 1);
            if ($blocked) {
                // 接收方把发送方拉黑了
                DeleteMessageForPrivateModel::u_insertGetId($base->identifier , $param['other_id'] , $id , $param['chat_id']);
            }
            SessionUtil::createOrUpdate($base->identifier , $param['user_id'] , 'private' , $param['chat_id']);
            DB::commit();
            $msg = MessageModel::findById($id);
            MessageUtil::handleMessage($msg , $param['user_id'] , $param['other_id']);
            /**
             * 投递到异步任务
             */
            WebSocket::deliveryTask(json_encode([
                'type' => 'callback' ,
                'data' => [
                    'callback' => [self::class , 'sendForAsyncTask'] ,
                    'param' => [
                        $base->platform ,
                        $msg ,
                    ] ,
                ]
            ]));
            if ($push_all) {
                // 诸如一些服务端以某用户身份推送的消息（必须该方法要求发送消息必须有发送方）
                // 这种情况下就要求所有相关用户都能接收到消息
                $base->push($msg->user_id , 'private_message' , $msg);
            } else {
                $base->send($msg->user_id , 'private_message' , $msg);
            }
            $base->push($msg->user_id , 'refresh_session');
            $base->push($msg->user_id , 'refresh_unread_count');
            $base->push($msg->user_id , 'refresh_session_unread_count');
            $e_time = microtime(true);
            var_dump('env: ' . ENV . '; identifier: ' . $base->identifier . '; ' . date('Y-m-d H:i:s') . " 【chat_id: {$msg->chat_id}；sender: {$msg->user_id}】私聊消息发送成功，耗费时间：" . bcmul($e_time - $s_time , 1 , 3));
            return self::success($msg);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // 推送标题
    public static function getMessageByTypeAndMessage($type , $message)
    {
        switch ($type)
        {
            case 'image':
                $message = '[图片]';
                break;
            case 'voice':
                $message = '[语音]';
                break;
            case 'card':
                $message = '[名片]';
                break;
            case 'file':
                $message = '[文件]';
                break;
            case 'voice_call':
                $message = '[语音通话]';
                break;
            case 'video':
                $message = '[视频]';
                break;
            case 'message_set':
                $message = '[消息集合]';
                break;
        }
        return $message;
    }

    /**
     * 私聊 app 推送，异步队列执行程序
     */
    public static function queueTaskForPrivate($platform , $other_id , $msg)
    {
        $s_time = microtime(true);
        $msg = convert_obj($msg);
        // todo app 推送要额外的事件队列处理
        AppPushUtil::pushCheckForOther($msg->identifier , $platform , $msg->user_id , $other_id , function() use($msg , $other_id , $platform){
            $message = $msg->old == 1 ? $msg->message : AesUtil::decrypt($msg->message , $msg->aes_key , config('app.aes_vi'));
            $message = self::getMessageByTypeAndMessage($msg->type , $message);
            $res = AppPushUtil::pushForPrivate($platform , $other_id , $message , '你收到了一条好友消息' , [
                'id' => $msg->id ,
                'user_id' => $msg->user_id ,
                'type' => $msg->type ,
                'chat_id' => $msg->chat_id ,
                'name'  => $msg->user->nickname ,
                'extra'  => $msg->extra ,
            ] , false);
            if ($res['code'] != 200) {
                ProgramErrorLogModel::u_insertGetId("Notice: App推送失败 [chat_id: {$msg->chat_id}] [sender: {$msg->user_id}; receiver: {$other_id}]");
            }
        });
        $e_time = microtime(true);
        var_dump('env: ' . ENV . '; identifier: ' . $msg->identifier . '; ' . date('Y-m-d H:i:s') . " 【chat_id: {$msg->chat_id}；sender: {$msg->user_id}】私聊队列任务（App 推送）执行完毕，耗费时间：" . bcmul($e_time - $s_time , 1 , 3));
    }

    /**
     * 私聊消息发送异步任务（接收方 + 发送方其他客户端）
     */
    public static function sendForAsyncTask(string $platform , $msg)
    {
        $s_time = microtime(true);
        $msg = convert_obj($msg);
        if ($msg->blocked == 1) {
            // 接收方已经把发送方加入黑名单
            return ;
        }
        $other_id = ChatUtil::otherId($msg->chat_id , $msg->user_id);
        SessionUtil::createOrUpdate($msg->identifier , $other_id , 'private' , $msg->chat_id);
        $relation = FriendData::findByIdentifierAndUserIdAndFriendId($msg->identifier , $other_id , $msg->user_id);
        if (
            $msg->type == 'voice_call'
//            $msg->type == 'voice_call' ||
//            (
//                $relation &&
//                $relation->can_notice == 0
//            )
        ) {
            // 语音通话-默认是已读的
            MessageReadStatusData::insertGetId($msg->identifier , $other_id , $msg->chat_id , $msg->id , 1);
        }
        if (empty($relation)) {
            $msg->user->nickname = empty($relation->alias) ? $msg->user->nickname : $relation->alias;
        }
        $msg->self_is_read  = MessageReadStatusData::isReadByIdentifierAndUserIdAndMessageId($msg->identifier , $other_id , $msg->id);
        $msg->other_is_read = MessageReadStatusData::isReadByIdentifierAndUserIdAndMessageId($msg->identifier , $msg->user_id , $msg->id);;
        PushUtil::single($msg->identifier , $other_id , 'private_message' , $msg);
        PushUtil::single($msg->identifier , $other_id , 'refresh_session');
        PushUtil::single($msg->identifier , $other_id , 'refresh_unread_count');
        PushUtil::single($msg->identifier , $other_id , 'refresh_session_unread_count');
        // 系统内推送
        AppPushUtil::pushCheckWithNewForOther($msg->identifier , $msg->user_id , $other_id , function() use($msg , $other_id){
            PushUtil::single($msg->identifier , $other_id , 'new');
        });
        // 将 app 推送添加到队列中
        QueueRedis::push(json_encode([
            'callback' => [self::class , 'queueTaskForPrivate'] ,
            'param' => [
                $platform ,
                $other_id ,
                $msg
            ] ,
        ]));
        $e_time = microtime(true);
        var_dump('env: ' . ENV . '; identifier: ' . $msg->identifier . '; ' . date('Y-m-d H:i:s') .  " 【chat_id: {$msg->chat_id}；sender: {$msg->user_id}】私聊异步任务执行完毕（推送消息给接受方成功），耗费时间：" . bcmul($e_time - $s_time , 1 , 3));
    }


    /**
     * 群聊消息发送异步任务（接收方 + 发送方其他其他客户端）
     */
    public static function groupSendForAsyncTask($platform , array $user_ids , string $target_user , $target_user_ids , $msg)
    {
        $s_time = microtime(true);
        // 获取群成员，过滤掉自身
        $msg = convert_obj($msg);
        $target_user_ids = $target_user == 'designation' ? json_decode($target_user_ids , true) : [];
        foreach ($user_ids as $v)
        {
            if ($v == $msg->user_id) {
                continue ;
            }
//            $s_time1 = microtime(true);
            // 消息已读未读
            SessionUtil::createOrUpdate($msg->identifier , $v , 'group' , $msg->group_id);
//            $relation = GroupMemberData::findByIdentifierAndGroupIdAndUserId($msg->identifier , $msg->group_id , $v);
//            if (!empty($relation)) {
//                if ($relation->can_notice == 0) {
//                    // 接收方开启了消息免打扰
//                    GroupMessageReadStatusData::insertGetId($msg->identifier , $v , $msg->id , $msg->group_id , 1);
//                }
//            }
            $msg->is_read = GroupMessageReadStatusData::isReadByIdentifierAndUserIdAndGroupMessageId($msg->identifier , $v , $msg->id);
            PushUtil::single($msg->identifier , $v , 'group_message' , $msg);
            PushUtil::single($msg->identifier , $v , 'refresh_session');
            PushUtil::single($msg->identifier , $v , 'refresh_unread_count');
            PushUtil::single($msg->identifier , $v , 'refresh_session_unread_count');
            // 添加到异步队列的速度正常来说应该是没有任何影响的
            // 系统内推送
//            var_dump($target_user);
            if (
                ($target_user == 'all' && $absolute = true) ||
                $target_user != 'designation' ||
                (
                    in_array($v , $target_user_ids) &&
                    ($absolute = true)
                )
            ) {
//                var_dump('推送的群成员 user_id: ' . $v);
                AppPushUtil::pushCheckWithNewForGroup($v , $msg->group_id , function() use($v , $msg){
                    PushUtil::single($msg->identifier , $v , 'new');
                });
                // app 推送添加到异步消息队列
                QueueRedis::push(json_encode([
                    'callback' => [self::class , 'queueTaskForGroup'] ,
                    'param' => [
                        $platform ,
                        $v ,
                        $msg ,
                        $absolute ?? false
                    ]
                ]));
            }
//            $e_time1 = microtime(true);
//            var_dump("单次循环花费多少时间：" . bcmul($e_time1 - $s_time1 , 1 , 3));
        }
        $e_time = microtime(true);
        var_dump('env: ' . ENV . '; identifier: ' . $msg->identifier . '; ' . date('Y-m-d H:i:s') . " 【group_id：[{$msg->group_id}]；sender: {$msg->user_id} 】群聊异步任务执行完毕（消息推送给接收方完成），耗费时间：" . bcmul($e_time  - $s_time , 1 , 3));
    }

    /**
     * 群聊队列事件
     */
    public static function queueTaskForGroup(string $platform , int $user_id , $msg , bool $absolute = false)
    {
        $s_time = microtime(true);
        $msg = convert_obj($msg);
        AppPushUtil::pushCheckForGroup($platform , $user_id , $msg->group_id , function() use($platform , $user_id , $msg){
            $message = $msg->old == 1 ? $msg->message : AesUtil::decrypt($msg->message , $msg->aes_key , config('app.aes_vi'));
            $message = self::getMessageByTypeAndMessage($msg->type , $message);
            // extra 极光推送 4000 Byte 长度限制
            $res = AppPushUtil::pushForGroup($platform , $user_id , $message , '你收到了一条群消息' , [
                'id'        => $msg->id ,
                'group_id'   => $msg->group_id ,
                'name'      => $msg->group->name ,
                'type'      => $msg->type ,
                'extra'      => $msg->extra ,
            ] , false);
//            var_dump("群聊app推送结果：group_id: {$msg->group_id}，receiver: {$user_id}；推送的结果：" . json_encode($res));
            if ($res['code'] != 200) {
                ProgramErrorLogModel::u_insertGetId("Notice: App推送失败 [group_id: {$msg->group_id}] [sender: {$msg->user_id}; receiver: {$user_id}]");
            }
        } , $absolute);
        $e_time = microtime(true);
        var_dump('env: ' . ENV . '; identifier: ' . $msg->identifier . '; ' . date('Y-m-d H:i:s') . " 【group_id：[{$msg->group_id}]；sender: {$msg->user_id} 】群聊队列任务（App 推送）执行完毕耗费时间：" . bcmul($e_time  - $s_time , 1 , 3));

    }

    /**
     * 群聊消息发送
     */
    public static function groupSend(Base $base , array $param , bool $push_all = false)
    {
        $s_time = microtime(true);
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
        $param['target_user_ids']   = $param['target_user_ids'] ?? '';
        // 如果用户没有指定推送的人，那么群推送
        $param['target_user'] = in_array($param['target_user'] ,  $group_target_user) ? $param['target_user'] : 'auto';
        $param['old'] = $param['old'] ?? '';
        $param['old'] = $param['old'] === '' ? 1 : $param['old'];
        $param['aes_key'] = $param['aes_key'] ?? $user->aes_key;
        $param['identifier'] = $base->identifier;
        $param['create_time'] = $param['create_time'] ?? '';
        $param['create_time'] = empty($param['create_time']) ? date('Y-m-d H:i:s') : $param['create_time'];
        // 将 html 标签转义成 html 实体
        if ($param['old'] < 1) {
            $aes_vi = config('app.aes_vi');
            $message = AesUtil::decrypt($param['message'] , $param['aes_key'] , $aes_vi);
            $message = strip_tags($message);
            // 重新加密
            $param['message'] = AesUtil::encrypt($message , $param['aes_key'] , $aes_vi);
        } else {
            $param['message'] = strip_tags($param['message']);
        }
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
                'identifier' ,
                'create_time' ,
            ]));
            $self_is_read = 1;
            GroupMessageReadStatusData::insertGetId($base->identifier , $param['user_id'] , $group_message_id , $param['group_id'] , $self_is_read);
            SessionUtil::createOrUpdate($base->identifier , $param['user_id'] , 'group' , $param['group_id']);
            DB::commit();
            $msg = GroupMessageModel::findById($group_message_id);
            $msg->user = $msg->user ?? new class() {};
            $msg->user->nickname = empty($member->alias) ? $msg->user->nickname : $member->alias;
            MessageUtil::handleGroupMessage($msg);

            // 群成员
            $user_ids = GroupMemberModel::getUserIdByGroupId($msg->group_id);
            /**
             * 投递异步任务
             */
            WebSocket::deliveryTask(json_encode([
                'type' => 'callback' ,
                'data' => [
                    'callback' => [self::class , 'groupSendForAsyncTask'] ,
                    'param' => [
                        $base->platform ,
                        $user_ids ,
                        $param['target_user'] ,
                        $param['target_user_ids'] ,
                        $msg ,
                    ]
                ] ,
            ]));
            $msg->is_read = $self_is_read;
            if ($push_all) {
                $base->push($msg->user_id , 'group_message' , $msg);
            } else {
                $base->send($msg->user_id , 'group_message' , $msg);
            }
            $base->push($msg->user_id , 'refresh_session');
            $base->push($msg->user_id , 'refresh_unread_count');
            $base->push($msg->user_id , 'refresh_session_unread_count');
            $e_time = microtime(true);
            var_dump('env: ' . ENV . '; identifier: ' . $base->identifier . '; ' . date('Y-m-d H:i:s') . " 【group_id：[{$msg->group_id}]；sender: {$msg->user_id} 】群聊消息发送成功，耗费时间：" . bcmul($e_time  - $s_time , 1 , 3));
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
                    AppPushUtil::pushCheckForGroup($base->platform , $param['user_id'] , $group->id , function() use($base , $v , $param , $msg){
                        $res = AppPushUtil::pushForGroup($base->platform , $v , $msg->message , '你收到了一条群消息' , $msg);
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
                AppPushUtil::pushCheckForGroup($base->platform , $param['user_id'] , $group->id , function() use($base , $v , $param , $msg){
                    $res = AppPushUtil::pushForGroup($base->platform , $v , $msg->message , '你收到了一条群消息' , $msg);
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