<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/20
 * Time: 11:51
 */

namespace App\WebSocket;


use App\WebSocket\Action\SessionAction;

class Session extends Auth
{
    // 会话列表
    public function session(array $param)
    {
        $res = SessionAction::session($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }
}