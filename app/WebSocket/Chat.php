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
        $res = ChatAction::send($this , 'voice' , $param);
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
        $res = ChatAction::send($this , 'card' , $param);
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
        $res = ChatAction::groupSend($this , 'card' , $param);
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
        $res = ChatAction::advoise($this , 'voice' , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }
}