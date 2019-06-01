<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 21:32
 */

namespace App\WebSocket;


use App\WebSocket\Action\MessageAction;
use App\WebSocket\Action\UserAction;

class Message extends Auth
{
    //  群：历史消息记录
    public function groupHistory(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $param['group_message_id'] = $param['group_message_id'] ?? '';
        $res = MessageAction::groupHistory($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 群：最近消息
    public function groupRecent(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $res = MessageAction::groupRecent($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 会话列表
    public function session(array $param)
    {
        $res = MessageAction::session($this->user);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 获取未读消息总数
    public function unreadCount(array $param)
    {
        $res = MessageAction::unreadCount($this);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 更新未读消息数量
    public function resetGroupUnread(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $res = MessageAction::resetGroupUnread($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }
}