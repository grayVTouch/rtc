<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/11
 * Time: 12:28
 */

namespace App\WebSocket;

use App\WebSocket\Action\UserAction;

class User extends Auth
{
    // 获取平台咨询通道信息
    public function groupForAdvoise(array $param)
    {
        $res = UserAction::groupForAdvoise($this);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    public function info()
    {
        return self::success($this->user);
    }
}