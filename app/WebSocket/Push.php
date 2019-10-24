<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/22
 * Time: 10:46
 */

namespace App\WebSocket;


use App\WebSocket\Action\PushAction;

class Push extends Auth
{
    // 更新推送消息读取状态
    public function readStatus(array $param)
    {
        $param['push_id'] = $param['push_id'] ?? '';
        $param['is_read'] = $param['is_read'] ?? '';
        $res = PushAction::readStatus($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 获取未读推送消息（总是获取给定数量的未读消息数量）
    public function unread(array $param)
    {
        $res = PushAction::unread($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

}