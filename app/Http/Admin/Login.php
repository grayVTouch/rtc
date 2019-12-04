<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/11
 * Time: 23:57
 */

namespace App\Http\Admin;

use App\Http\Admin\Action\LoginAction;

class Login extends Base
{
    // 注册
    public function register()
    {
        $param = $this->request->post;
        $param['role'] = $param['role'] ?? '';
        $res = LoginAction::register($param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 远程登录
    public function login()
    {
        $param = $this->request->post;
        $param['unique_code'] = $param['unique_code'] ?? '';
        $res = LoginAction::login($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);

        // test 11
    }
}
