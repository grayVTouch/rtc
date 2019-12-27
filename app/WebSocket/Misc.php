<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/27
 * Time: 9:40
 */

namespace App\WebSocket;


use App\WebSocket\Action\MiscAction;

class Misc extends Auth
{
    // 检查是国内还是国外
    public function outside(array $param)
    {
        $res = MiscAction::outside($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }


}