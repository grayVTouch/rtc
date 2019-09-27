<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/11
 * Time: 23:57
 */

namespace App\Http;

use App\Http\Action\LoginAction;

class Login extends Base
{
    // 注册
    public function register()
    {
        $param = $this->request->post;
        $param['identifier'] = $param['identifier'] ?? '';
        $param['username'] = $param['username'] ?? '';
        $param['password'] = $param['password'] ?? '';
        $param['nickname'] = $param['nickname'] ?? '';
        $param['avatar'] = $param['avatar'] ?? '';
        $param['role'] = $param['role'] ?? '';
        $res = LoginAction::register($param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 远程登录
    public function remoteLogin()
    {
        $param = $this->request->post;
        $param['unique_code'] = $param['unique_code'] ?? '';
        $res = LoginAction::remoteLogin($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 登录
    public function login()
    {
        $param = $this->request->post;
        $param['username'] = $param['username'] ?? '';
        $param['password'] = $param['password'] ?? '';
        $res = LoginAction::login($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }
}
