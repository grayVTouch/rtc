<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/18
 * Time: 23:38
 */

namespace App\Http\Admin\Controller;


use App\Http\Admin\Action\PushAction;

class Push extends Auth
{
    // 消息推送
    public function push()
    {
        $param = $this->request->post;
        $param['identifier'] = $param['identifier'] ?? '';
        $param['push_type'] = $param['push_type'] ?? '';
        $param['type'] = $param['type'] ?? '';
        $param['role'] = $param['role'] ?? '';
        $param['user_id'] = $param['user_id'] ?? '';
        $param['title'] = $param['title'] ?? '';
        $param['desc'] = $param['desc'] ?? '';
        $param['content'] = $param['content'] ?? '';
        $res = PushAction::push($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

}