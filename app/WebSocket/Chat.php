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

    /**
     * 平台咨询-文本
     *
     * @param array $param
     * @return mixed
     */
    public function advoiseForText(array $param)
    {
        $param['group_id']  = $param['group_id'] ?? '';
        $param['message']   = $param['message'] ?? '';
        $res = ChatAction::advoise($this , 'text' , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 消息发送：文本
    public function sendTextForPrivate(array $param)
    {
        $param['friend_id'] = $param['friend_id'] ?? '';
        $param['message']   = $param['message'] ?? '';
        $res = ChatAction::send($this , 'text' , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 消息发送：图片
    public function sendImageForPrivate(array $param)
    {
        $param['friend_id'] = $param['friend_id'] ?? '';
        $param['message']   = $param['message'] ?? '';
        $res = ChatAction::send($this , 'image' , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 消息发送：语音
    public function sendVoiceForPrivate(array $param)
    {
        $param['friend_id'] = $param['friend_id'] ?? '';
        $param['message']   = $param['message'] ?? '';
        $res = ChatAction::send($this , 'voice' , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

}