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
    public function loginUseUniqueCode(array $param)
    {
        $param['unique_code'] = $param['unique_code'] ?? '';
        $res = LoginAction::loginUseUniqueCode($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    public function loginUseUsername(array $param)
    {
        $param['username'] = $param['username'] ?? '';
        $param['password'] = $param['password'] ?? '';
        $res = LoginAction::loginUseUsername($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    public function loginUsePhone(array $param)
    {
        print_r($param);
        $param['area_code'] = $param['area_code'] ?? '';
        $param['phone'] = $param['phone'] ?? '';
        $param['sms_code'] = $param['sms_code'] ?? '';
        $res = LoginAction::loginUsePhone($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 注册
    public function registerUsePhone(array $param)
    {
        $param['role'] = $param['role'] ?? '';
        $param['area_code'] = $param['area_code'] ?? '';
        $param['phone'] = $param['phone'] ?? '';
        $param['password'] = $param['phone'] ?? '';
        $param['confirm_password'] = $param['phone'] ?? '';
        $param['sms_code'] = $param['sms_code'] ?? '';
        $param['invite_code'] = $param['invite_code'] ?? '';
        $res = LoginAction::registerUsePhone($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    public function registerUsePhoneV1(array $param)
    {
        $param['role'] = $param['role'] ?? '';
        $param['area_code'] = $param['area_code'] ?? '';
        $param['phone'] = $param['phone'] ?? '';
        $param['nickname'] = $param['nickname'] ?? '';
        $param['sms_code'] = $param['sms_code'] ?? '';
        $param['invite_code'] = $param['invite_code'] ?? '';
        $res = LoginAction::registerUsePhoneV1($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 注册短信验证码
    public function smsCodeForRegister(array $param)
    {
        $param['area_code'] = $param['area_code'] ?? '';
        $param['phone'] = $param['phone'] ?? '';
        print_r($param);
        $res = LoginAction::smsCode($this , 1 , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 登录验证码
    public function smsCodeForLogin(array $param)
    {
        $param['area_code'] = $param['area_code'] ?? '';
        $param['phone'] = $param['phone'] ?? '';
        $res = LoginAction::smsCode($this , 2 , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 修改密码验证码
    public function smsCodeForPassword(array $param)
    {
        $param['area_code'] = $param['area_code'] ?? '';
        $param['phone'] = $param['phone'] ?? '';
        $res = LoginAction::smsCode($this , 3 , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }
}