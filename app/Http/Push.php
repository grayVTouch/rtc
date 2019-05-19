<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/18
 * Time: 23:38
 */

namespace App\Http;


use App\Http\Action\PushAction;

class Push extends Auth
{
    // 推送：单个人
    public function single()
    {
        $param = $this->request->post;
        $param['user_id'] = $param['user_id'] ?? '';
        $param['type'] = $param['type'] ?? '';
        $param['data'] = $param['data'] ?? '';
        $res = PushAction::single($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 推送：一批人
    public function multiple()
    {
        $param = $this->request->post;
        $param['role'] = $param['role'] ?? '';
        $param['type'] = $param['type'] ?? '';
        $param['data'] = $param['data'] ?? '';
        $res = PushAction::multiple($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }
}