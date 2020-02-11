<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/26
 * Time: 9:34
 */

namespace App\WebSocket\V1\Controller;


use App\WebSocket\V1\Action\HeartAction;

class Heart extends Base
{
    // 心跳检查
    public function ping(array $param)
    {
        $res = HeartAction::ping($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }
}