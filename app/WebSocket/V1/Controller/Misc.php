<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/27
 * Time: 9:40
 */

namespace App\WebSocket\V1\Controller;


use App\WebSocket\V1\Action\MiscAction;

class Misc extends Auth
{
    // 检查是国内还是国外
    public function outside(array $param)
    {
        $res = MiscAction::outside($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 翻译
    public function translate(array $param)
    {
        $param['value'] = $param['value'] ?? '';
        $res = MiscAction::translate($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

}