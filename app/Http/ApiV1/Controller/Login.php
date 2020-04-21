<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/4/21
 * Time: 10:18
 */

namespace App\Http\ApiV1\Controller;


use App\Http\ApiV1\Action\LoginAction;

class Login extends Base
{
    // 开放给第三方的用户账号相关接口
    public function login()
    {
        $param = $this->request->post;
        $res = LoginAction::login($this , $param);
        if ($res['code'] != 0) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    public function register()
    {
        $param = $this->request->post;
        $param['nickname'] = $param['nickname'] ?? '';
        $param['avatar'] = $param['avatar'] ?? '';
        $res = LoginAction::login($this , $param);
        if ($res['code'] != 0) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }
}