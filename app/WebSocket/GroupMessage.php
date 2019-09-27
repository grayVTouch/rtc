<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/20
 * Time: 10:50
 */

namespace App\WebSocket;


use App\WebSocket\Action\GroupMessageAction;
use App\WebSocket\Action\UserAction;

class GroupMessage extends Auth
{
    // 重置群未读消息数量
    public function resetGroupUnread(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $res = GroupMessageAction::resetGroupUnread($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    //  群：历史消息记录
    public function groupHistory(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $param['group_message_id'] = $param['group_message_id'] ?? '';
        $res = GroupMessageAction::groupHistory($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 群：最近消息
    public function groupRecent(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $res = GroupMessageAction::groupRecent($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }
}