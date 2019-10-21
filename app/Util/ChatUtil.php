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
use App\Model\UserOptionModel;
use App\Redis\UserRedis;
use App\WebSocket\Base;
use App\WebSocket\Util\MessageUtil;
use function core\array_unit;
use Core\Lib\Throwable;
use Core\Lib\Validator;
use Exception;
use Illuminate\Support\Facades\DB;
use Push\AppPush;


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
        // 该条消息是否是阅后即焚的消息
        $param['flag'] = empty($relation) ? 'normal' :
            ($relation->burn == 1 ? 'burn' : 'normal');
        $param['chat_id'] = ChatUtil::chatId($param['user_id'] , $param['friend_id']);
        $param['extra'] = $param['extra'] ?? '';
        // 这边做基本的认证
        $blocked = BlacklistModel::blocked($param['user_id'] , $param['user_id']);
        $param['blocked'] = (int) $blocked;
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
            } else {
                SessionUtil::createOrUpdate($param['friend_id'] , 'private' , $param['chat_id']);
            }
            $msg = MessageModel::findById($id);
            SessionUtil::createOrUpdate($param['user_id'] , 'private' , $param['chat_id']);
            MessageUtil::handleMessage($msg , $param['user_id'] , $param['friend_id']);
            DB::commit();
            if (!$blocked) {
                $user_ids = [$param['user_id'] , $param['friend_id']];
                var_dump(json_encode($user_ids));
                var_dump('当前登录用户【user_id: ' . UserRedis::fdMappingUserId($base->identifier , $base->fd) . '】的 fd' . $base->fd);
                foreach ($user_ids as $v)
                {
                    $fd = UserRedis::userIdMappingFd($base->identifier , $v);
                    var_dump('这边推送的用户id【' . $v . '】对应的 fd' . json_encode($fd));
                }
                if ($push_all) {
                    // 用于消息转发
                    $base->pushAll($user_ids , 'private_message' , $msg);
                } else {
                    // 用于正常聊天
                    $base->sendAll($user_ids , 'private_message' , $msg);
                }
                $base->pushAll($user_ids , 'refresh_session');
                $base->pushAll($user_ids , 'refresh_unread_count');
                AppPushUtil::pushCheckForFriend($base->platform , $param['user_id'] , $param['friend_id'] , function() use($param , $msg){
                    $res = AppPushUtil::pushForPrivate($param['friend_id'] , $msg->message , '你收到了一条好友消息' , $msg);
                    if ($res['code'] != 200) {
                        ProgramErrorLogModel::u_insertGetId("Notice: App推送失败 [chat_id: {$param['chat_id']}] [sender: {$param['user_id']}; receiver: {$param['friend_id']}]");
                    }
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
        // 检查是否时好友
        $exist = GroupMemberModel::exist($param['user_id'] , $param['group_id']);
        if (!$exist) {
            return self::error('您不在该群，禁止操作' , 403);
        }
        $param['extra'] = $param['extra'] ?? '';
        try {
            DB::beginTransaction();
            $group_message_id = GroupMessageModel::insertGetId(array_unit($param , [
                'user_id' ,
                'group_id' ,
                'message' ,
                'type' ,
                'extra' ,
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
            $base->sendAll($user_ids , 'group_message' , $msg);
            $base->pushAll($user_ids , 'refresh_session');
            $base->pushAll($user_ids , 'refresh_unread_count');
            foreach ($user_ids as $v)
            {
                if ($v == $param['user_id']) {
                    // 跳过发送消息的人
                    continue ;
                }
                // string $platform , int $user_id , int $group_id , callable $callback)
                AppPushUtil::pushCheckForGroup($base->platform , $param['user_id'] , $group->id , function() use($v , $param , $msg){
                    $res = AppPushUtil::pushForGroup($v , $msg->message , '你收到了一条群消息' , $msg);
                    if ($res['code'] != 200) {
                        ProgramErrorLogModel::u_insertGetId("Notice: App推送失败 [group_id: {$param['group_id']}] [sender: {$param['user_id']}; receiver: {$v}]");
                    }
                });
            }
            return self::success($msg);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // 获取好友
    public static function receiver(string $chat_id , int $sender)
    {
        if (empty($chat_id)) {
            return 0;
        }
        $res = explode('_' , $chat_id);
        foreach ($res as $v)
        {
            if ($v == $sender) {
                continue ;
            }
            return $v;
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
}