<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 9:06
 */

namespace App\WebSocket;

use App\WebSocket\Action\LoginAction;

class Login extends Base
{
    public function login(array $param)
    {
        $param['unique_code'] = $param['unique_code'] ?? '';
        $res = LoginAction::login($param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($param['data']);
    }
}