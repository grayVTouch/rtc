<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/14
 * Time: 23:37
 */

namespace App\WebSocket;

use App\WebSocket\Action\ChatAction;

class Chat extends Auth
{
    // 私聊消息发送：文本
    public function sendTextForPrivate(array $param)
    {
        $param['friend_id'] = $param['friend_id'] ?? '';
        $param['message']   = $param['message'] ?? '';
        $param['extra']     = $param['extra'] ?? '';
        $param['old']     = $param['old'] ?? '';
        $param['create_time']     = $param['create_time'] ?? '';
        $res = ChatAction::send($this , 'text' , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 私聊消息发送：图片
    public function sendImageForPrivate(array $param)
    {
        $param['friend_id'] = $param['friend_id'] ?? '';
        $param['message']   = $param['message'] ?? '';
        $param['extra']     = $param['extra'] ?? '';
        $param['old']     = $param['old'] ?? '';
        $param['create_time']     = $param['create_time'] ?? '';
        $res = ChatAction::send($this , 'image' , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 私聊消息发送：语音
    public function sendVoiceForPrivate(array $param)
    {
        $param['friend_id'] = $param['friend_id'] ?? '';
        $param['message']   = $param['message'] ?? '';
        $param['extra']     = $param['extra'] ?? '';
        $param['old']     = $param['old'] ?? '';
        $param['create_time']     = $param['create_time'] ?? '';
        $res = ChatAction::send($this , 'voice' , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 私聊消息发送：文件
    public function sendFileForPrivate(array $param)
    {
        $param['friend_id'] = $param['friend_id'] ?? '';
        $param['message']   = $param['message'] ?? '';
        $param['extra']     = $param['extra'] ?? '';
        $param['old']     = $param['old'] ?? '';
        $param['create_time']     = $param['create_time'] ?? '';
//        $res = ChatAction::send($this , 'file' , $param);
        $res = ChatAction::send($this , 'file' , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 私聊消息发送：视频
    public function sendVideoForPrivate(array $param)
    {
        $param['friend_id'] = $param['friend_id'] ?? '';
        $param['message']   = $param['message'] ?? '';
        $param['extra']     = $param['extra'] ?? '';
        $param['old']     = $param['old'] ?? '';
        $param['create_time']     = $param['create_time'] ?? '';
        $res = ChatAction::send($this , 'video' , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 私聊消息发送：名片
    public function sendCardForPrivate(array $param)
    {
        $param['friend_id'] = $param['friend_id'] ?? '';
        $param['message']   = $param['message'] ?? '';
        $param['extra']     = $param['extra'] ?? '';
        $param['old']     = $param['old'] ?? '';
        $param['create_time']     = $param['create_time'] ?? '';
        $res = ChatAction::send($this , 'card' , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 私聊消息发送：发送语音通话
    public function sendVoiceCallForPrivate(array $param)
    {
        $param['friend_id'] = $param['friend_id'] ?? '';
        $param['message']   = $param['message'] ?? '';
        $param['extra']     = $param['extra'] ?? '';
        $param['old']     = $param['old'] ?? '';
        $param['create_time']     = $param['create_time'] ?? '';
        $res = ChatAction::send($this , 'voice_call' , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 私聊：语音通话 发起方挂断通话
    public function senderRefuseVoiceCallForPrivate(array $param)
    {
        $param['message_id'] = $param['message_id'] ?? '';
        $param['end_time'] = $param['end_time'] ?? '';
        $res = ChatAction::updateVoiceCallStatusForPrivate($this , 'hang' , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 私聊：语音通话 接收方挂断通话
    public function receiverRefuseVoiceCallForPrivate(array $param)
    {
        $param['message_id'] = $param['message_id'] ?? '';
        $param['end_time'] = $param['end_time'] ?? '';
        $res = ChatAction::updateVoiceCallStatusForPrivate($this , 'refuse' , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 私聊：语音通话 接收方接听通话
    public function receiverAcceptVoiceCallForPrivate(array $param)
    {
        $param['message_id'] = $param['message_id'] ?? '';
        $param['end_time'] = $param['end_time'] ?? '';
        $res = ChatAction::updateVoiceCallStatusForPrivate($this , 'accept' , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 私聊：语音通话 记录语音通话成功后的挂断时间
    public function logVoiceCallCloseTimeForPrivate(array $param)
    {
        $param['message_id'] = $param['message_id'] ?? '';
        $param['close_time'] = $param['close_time'] ?? '';
        $param['duration'] = $param['duration'] ?? '';
        $res = ChatAction::logVoiceCallCloseTimeForPrivate($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }



    // 群消息发送：文本
    public function sendTextForGroup(array $param)
    {
        $param['group_id']  = $param['group_id'] ?? '';
        $param['message']   = $param['message'] ?? '';
        $param['extra']     = $param['extra'] ?? '';
        $param['target_user'] = $param['target_user'] ?? '';
        $param['target_user_ids'] = $param['target_user_ids'] ?? '';
        $param['old']     = $param['old'] ?? '';
        $param['create_time']     = $param['create_time'] ?? '';
        $res = ChatAction::groupSend($this , 'text' , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 群消息发送：图片
    public function sendImageForGroup(array $param)
    {
        $param['group_id']  = $param['group_id'] ?? '';
        $param['message']   = $param['message'] ?? '';
        $param['extra']     = $param['extra'] ?? '';
        $param['target_user'] = $param['target_user'] ?? '';
        $param['target_user_ids'] = $param['target_user_ids'] ?? '';
        $param['old']     = $param['old'] ?? '';
        $param['create_time']     = $param['create_time'] ?? '';
        $res = ChatAction::groupSend($this , 'image' , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 群消息发送：语音
    public function sendVoiceForGroup(array $param)
    {
        $param['group_id']  = $param['group_id'] ?? '';
        $param['message']   = $param['message'] ?? '';
        $param['extra']     = $param['extra'] ?? '';
        $param['target_user'] = $param['target_user'] ?? '';
        $param['target_user_ids'] = $param['target_user_ids'] ?? '';
        $param['old']     = $param['old'] ?? '';
        $param['create_time']     = $param['create_time'] ?? '';
        $res = ChatAction::groupSend($this , 'voice' , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 群消息发送：名片
    public function sendCardForGroup(array $param)
    {
        $param['group_id']  = $param['group_id'] ?? '';
        $param['message']   = $param['message'] ?? '';
        $param['extra']     = $param['extra'] ?? '';
        $param['target_user'] = $param['target_user'] ?? '';
        $param['target_user_ids'] = $param['target_user_ids'] ?? '';
        $param['old']     = $param['old'] ?? '';
        $param['create_time']     = $param['create_time'] ?? '';
        $res = ChatAction::groupSend($this , 'card' , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 群消息发送：语音
    public function sendFileForGroup(array $param)
    {
        $param['group_id']  = $param['group_id'] ?? '';
        $param['message']   = $param['message'] ?? '';
        $param['extra']     = $param['extra'] ?? '';
        $param['target_user'] = $param['target_user'] ?? '';
        $param['target_user_ids'] = $param['target_user_ids'] ?? '';
        $param['old']     = $param['old'] ?? '';
        $param['create_time']     = $param['create_time'] ?? '';
        $res = ChatAction::groupSend($this , 'file' , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 群消息发送：语音
    public function sendVideoForGroup(array $param)
    {
        $param['group_id']  = $param['group_id'] ?? '';
        $param['message']   = $param['message'] ?? '';
        $param['extra']     = $param['extra'] ?? '';
        $param['target_user'] = $param['target_user'] ?? '';
        $param['target_user_ids'] = $param['target_user_ids'] ?? '';
        $param['old']     = $param['old'] ?? '';
        $param['create_time']     = $param['create_time'] ?? '';
        $res = ChatAction::groupSend($this , 'video' , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    /**
     * 平台咨询-文本
     *
     * @param array $param
     * @return mixed
     */
    public function sendTextForAdvoise(array $param)
    {
        $param['group_id']  = $param['group_id'] ?? '';
        $param['message']   = $param['message'] ?? '';
        $param['extra']   = $param['extra'] ?? '';
        $param['old']     = $param['old'] ?? '';
        $res = ChatAction::advoise($this , 'text' , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    /**
     * 平台咨询-图片
     *
     * @param array $param
     * @return mixed
     */
    public function sendImageForAdvoise(array $param)
    {
        $param['group_id']  = $param['group_id'] ?? '';
        $param['message']   = $param['message'] ?? '';
        $param['extra']   = $param['extra'] ?? '';
        $param['old']     = $param['old'] ?? '';
        $res = ChatAction::advoise($this , 'image' , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    /**
     * 平台咨询-语音
     *
     * @param array $param
     * @return mixed
     */
    public function sendVoiceForAdvoise(array $param)
    {
        $param['group_id']  = $param['group_id'] ?? '';
        $param['message']   = $param['message'] ?? '';
        $param['extra']     = $param['extra'] ?? '';
        $param['old']     = $param['old'] ?? '';
        $res = ChatAction::advoise($this , 'voice' , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 记录开始通话时间
    public function logVoiceCallStartTime(array $param)
    {
        $param['message_id'] = $param['message_id'] ?? '';
        $param['start_time'] = $param['start_time'] ?? '';
        $res = ChatAction::logVoiceCallStartTime($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }
}