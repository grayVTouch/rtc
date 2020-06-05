<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/4/16
 * Time: 9:47
 */

namespace App\Http\ApiV1\Controller;


use App\Http\WebV1\Action\PushNoAuthAction;

class PushNoAuth extends Base
{
    // 群发推送-通知
    public function notifyAll()
    {
        $param = $this->request->post();
        $param['user_id'] = $param['user_id'] ?? '';
        // 推送的数据
        $param['type'] = $param['type'] ?? '';
        $param['data'] = $param['data'] ?? '';
        $res = PushNoAuthAction::notifyAll($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 单个用户推送
    public function notify()
    {
        $param = $this->request->post();
        $param['user_id'] = $param['user_id'] ?? '';
        // 推送的数据
        $param['type'] = $param['type'] ?? '';
        $param['data'] = $param['data'] ?? '';
        $res = PushNoAuthAction::notify($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

}