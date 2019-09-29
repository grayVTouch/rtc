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
    public function resetUnread(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $res = GroupMessageAction::resetUnread($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    //  群：历史消息记录
    public function history(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $param['limit_id'] = $param['limit_id'] ?? '';
        $res = GroupMessageAction::history($this, $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'], $res['code']);
        }
        return $this->success($res['data']);
    }
}