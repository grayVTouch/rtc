<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/11
 * Time: 11:19
 */

namespace App\Util;


use App\Model\FriendModel;
use App\Model\GroupMemberModel;
use App\Model\GroupMessageModel;
use App\Model\GroupMessageReadStatusModel;
use App\Model\GroupModel;
use App\Model\MessageModel;
use App\Model\MessageReadStatusModel;
use App\Redis\UserRedis;
use App\WebSocket\Base;
use App\WebSocket\Util\MessageUtil;
use function core\array_unit;
use Core\Lib\Throwable;
use Core\Lib\Validator;
use Exception;
use Illuminate\Support\Facades\DB;
use function WebSocket\ws_config;

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
    public static function send(Base $base , array $param)
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
            // todo 这个地方可能需要返回一个特殊的状态码
            return self::error('你们还不是好友，禁止操作' , 403);
        }
        // 该条消息是否是阅后即焚的消息
        $param['flag'] = $relation->burn == 1 ? 'burn' : 'normal';
        $param['chat_id'] = ChatUtil::chatId($param['user_id'] , $param['friend_id']);
        $param['extra'] = $param['extra'] ?? '';
        // 这边做基本的认证
        try {
            DB::beginTransaction();
            $id = MessageModel::insertGetId(array_unit($param , [
                'user_id' ,
                'chat_id' ,
                'message' ,
                'type' ,
                'flag' ,
                'extra' ,
            ]));
            MessageReadStatusModel::initByMessageId($id , $param['user_id'] , $param['friend_id']);
            $msg = MessageModel::findById($id);
            MessageUtil::handleMessage($msg , $param['user_id'] , $param['friend_id']);
            DB::commit();
            $user_ids = [$param['user_id'] , $param['friend_id']];
            var_dump(json_encode($user_ids));
            var_dump('当前登录用户的 fd' . $base->fd);
            foreach ($user_ids as $v)
            {
                $fd = UserRedis::fdByUserId($base->identifier , $v);
                var_dump('这边推送的用户id对应的 json' . json_encode($fd));
            }
            $base->sendAll($user_ids , 'private_message' , $msg);
            $base->pushAll($user_ids , 'refresh_session');
            $base->pushAll($user_ids , 'refresh_unread_count');
            if (ws_config('app.enable_app_push')) {
                // todo app 推送
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
    public static function groupSend(Base $base , array $param)
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
            // todo 这个地方可能需要返回一个特殊的状态码
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
            DB::commit();
            $base->sendAll($user_ids , 'group_message' , $msg);
            $base->pushAll($user_ids , 'refresh_session');
            $base->pushAll($user_ids , 'refresh_unread_count');
            if (ws_config('app.enable_app_push')) {
                // todo app 推送
            }
            return self::success($msg);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}