<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 21:32
 */

namespace App\WebSocket;


use App\WebSocket\Action\MessageAction;


class Message extends Auth
{
    // 未读消息（私聊 + 群聊）
    public function unreadCountForCommunication(array $param)
    {
        $res = MessageAction::unreadCountForCommunication($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 推送消息
    public function unreadCountForPush(array $param)
    {
        $res = MessageAction::unreadCountForPush($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 总：通迅消息 + 推送消息 + 申请消息
    public function unreadCount(array $param)
    {
        $res = MessageAction::unreadCount($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 历史记录
    public function history(array $param)
    {
        $param['friend_id'] = $param['friend_id'] ?? '';
        $param['limit_id'] = $param['limit_id'] ?? '';
        $param['limit'] = $param['limit'] ?? '';
        $res = MessageAction::history($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 重置未读数量
    public function resetUnread(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $res = MessageAction::resetUnread($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 重置未读数量
    public function delete(array $param)
    {
        $param['message_id'] = $param['message_id'] ?? '';
        $res = MessageAction::delete($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }


}