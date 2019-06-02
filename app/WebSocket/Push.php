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
    // 未读消息数量
    public function unread(array $param)
    {
        $res = PushAction::unread($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }
}