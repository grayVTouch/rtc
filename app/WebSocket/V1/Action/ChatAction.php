<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 10:21
 */

namespace App\WebSocket\V1\Action;

use App\WebSocket\V1\Model\FriendModel;
use App\WebSocket\V1\Model\MessageModel;
use App\WebSocket\V1\Model\MessageReadStatusModel;
use App\WebSocket\V1\Model\GroupModel;
use App\WebSocket\V1\Model\GroupMemberModel;
use App\WebSocket\V1\Model\GroupMessageModel;
use App\WebSocket\V1\Model\GroupMessageReadStatusModel;
use App\WebSocket\V1\Redis\MessageRedis;
use App\WebSocket\V1\Redis\UserRedis;
use App\WebSocket\V1\Util\ChatUtil;
use App\WebSocket\V1\Util\MessageUtil;
use App\WebSocket\V1\Util\UserUtil;
use function core\array_unit;
use Core\Lib\Throwable;
use Core\Lib\Validator;
use Exception;
use Illuminate\Support\Facades\DB;
use App\WebSocket\V1\Controller\Auth;


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
        $param['user_id']   = $auth->user->id;
        $param['type']      = $type;
        return ChatUtil::advoise($auth , $param);
    }

    /**
     * 私聊消息发送
     *
     * @throws \Exception
     */
    public static function send(Auth $auth , $type , array $param = [])
    {
        $param['user_id']   = $auth->user->id;
        $param['other_id']  = $param['friend_id'];
        $param['type']      = $type;
        return ChatUtil::send($auth , $param);
    }

    /**
     * *******
     * 挂断电话
     * *******
     */
    public static function updateVoiceCallStatusForPrivate(Auth $auth , string $status , array $param)
    {
        $validator = Validator::make($param , [
            'message_id' => 'required' ,
            'end_time' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $voice_call_status = config('business.voice_call_status');
        if (!in_array($status , $voice_call_status)) {
            return self::error('不支持的语音状态' , 403);
        }
        $msg = MessageModel::findById($param['message_id']);
        if (empty($msg)) {
            return self::error('消息不存在' , 404);
        }
        // 检查下消息类型
        if ($msg->type != 'voice_call') {
            return self::error('消息类型错误，请提供 type = voice_call 的消息' , 403);
        }
        $extra = json_decode($msg->extra , true);
        if (empty($extra)) {
            return self::error('语音通话消息不完整' , 500);
        }
        $other_id = ChatUtil::otherId($msg->chat_id , $auth->user->id);
        $deny_voice_call_status = config('business.deny_voice_call_status');
        if (in_array($extra['status'] , $deny_voice_call_status)) {
            // 比较特殊
            MessageUtil::handleMessage($msg , $auth->user->id , $other_id);
            return self::success($msg);
        }
        $user_ids = ChatUtil::userIds($msg->chat_id);
        if (!in_array($auth->user->id , $user_ids)) {
            return self::error('您正在试图更改他人会话的消息，禁止操作' , 403);
        }
        $extra['status'] = $status;
        $extra['end_time'] = $param['end_time'];
        $extra['end_time_for_unix'] = strtotime($extra['end_time']);
        $extra_for_update = json_encode($extra);
        MessageModel::updateById($msg->id , [
            'extra' => $extra_for_update
        ]);
        $msg->extra = $extra_for_update;
        MessageUtil::handleMessage($msg , $other_id , $auth->user->id);
        // 通知对方已接听 还是 已挂断
        $auth->push($other_id , $status == 'accept' ? 'accept_voice_call' : 'close_voice_call' , $msg);
        $auth->push($other_id , 'refresh_private_message' , $msg);
        MessageUtil::handleMessage($msg , $auth->user->id , $other_id);
        $auth->send($auth->user->id , 'refresh_private_message' , $msg);
        $auth->pushAll($user_ids , 'refresh_session');
        return self::success($msg);
    }

    public static function logVoiceCallCloseTimeForPrivate(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'message_id' => 'required' ,
            'close_time' => 'required' ,
            'duration' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $msg = MessageModel::findById($param['message_id']);
        if (empty($msg)) {
            return self::error('消息不存在' , 404);
        }
        // 检查下消息类型
        if ($msg->type != 'voice_call') {
            return self::error('消息类型错误，请提供 type = voice_call 的消息' , 403);
        }
        $extra = json_decode($msg->extra , true);
        if (empty($extra)) {
            return self::error('语音通话消息不完整' , 500);
        }
        if ($extra['status'] != 'accept') {
            return self::error('该消息禁止操作' , 403);
        }
        $user_ids = ChatUtil::userIds($msg->chat_id);
        if (!in_array($auth->user->id , $user_ids)) {
            return self::error('您正在试图更改他人会话的消息，禁止操作' , 403);
        }
        $extra['close_time'] = $param['close_time'];
        $extra['close_time_for_unix'] = strtotime($param['close_time']);
        $extra['duration'] = $param['duration'];
        $extra_for_update = json_encode($extra);
        MessageModel::updateById($msg->id , [
            'extra' => $extra_for_update
        ]);
        $msg->extra = $extra_for_update;
        $other_id = ChatUtil::otherId($msg->chat_id , $auth->user->id);
        MessageUtil::handleMessage($msg , $other_id , $auth->user->id);
        // 通知对方已经挂断
        $auth->push($other_id , 'close_voice_call' , $msg);
        $auth->push($other_id , 'refresh_private_message' , $msg);
        MessageUtil::handleMessage($msg , $auth->user->id , $other_id);
        $auth->send($auth->user->id , 'refresh_private_message' , $msg);
        return self::success($msg);
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
        return ChatUtil::groupSend($auth , $param);
    }

}