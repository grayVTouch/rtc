<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/3/13
 * Time: 10:19
 */

namespace App\Http\WebV1\Controller;

use App\Http\WebV1\Action\ClientAction;

class Client extends Base
{
    // 消息转发
    public function push()
    {
        $param = $this->request->post;
        $param['client'] = $param['client'] ?? '';
        $param['exclude']   = $param['exclude'] ?? '';
        $param['data']   = $param['data'] ?? '';
        $res = ClientAction::push($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }
}