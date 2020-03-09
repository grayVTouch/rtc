<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 9:06
 */

namespace App\WebSocket\V1\Controller;

use App\WebSocket\V1\Action\LoginAction;
use function core\random;

class Login extends Base
{
    public function loginUseUniqueCode(array $param)
    {
        $param['unique_code'] = $param['unique_code'] ?? '';
        $res = LoginAction::loginUseUniqueCode($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    public function loginUseUsername(array $param)
    {
        $param['username'] = $param['username'] ?? '';
        $param['password'] = $param['password'] ?? '';
        $param['verify_code'] = $param['verify_code'] ?? '';
        $param['verify_code_key'] = $param['verify_code_key'] ?? '';
        $res = LoginAction::loginUseUsername($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    public function loginUseUsernameV1(array $param)
    {
        $param['username'] = $param['username'] ?? '';
        $param['password'] = $param['password'] ?? '';
        $param['verify_code'] = $param['verify_code'] ?? '';
        $param['verify_code_key'] = $param['verify_code_key'] ?? '';

        $param['device_code'] = $param['device_code'] ?? '';
        $param['gt_verify'] = $param['gt_verify'] ?? '';
        $res = LoginAction::loginUseUsernameV1($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    public function loginUsePhone(array $param)
    {
        $param['area_code'] = $param['area_code'] ?? '';
        $param['phone'] = $param['phone'] ?? '';
        $param['sms_code'] = $param['sms_code'] ?? '';
        $param['verify_code'] = $param['verify_code'] ?? '';
        $param['verify_code_key'] = $param['verify_code_key'] ?? '';

        $param['device_code'] = $param['device_code'] ?? '';
        $param['gt_verify'] = $param['gt_verify'] ?? '';
        $res = LoginAction::loginUsePhone($this , $param);
        if ($res['code'] != 0) {
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
        if ($res['code'] != 0) {
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
        $param['verify_code'] = $param['verify_code'] ?? '';
        $param['verify_code_key'] = $param['verify_code_key'] ?? '';
        $res = LoginAction::registerUsePhoneV1($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    public function registerUseUsername(array $param)
    {
        $param['role'] = $param['role'] ?? '';
        $param['username'] = $param['username'] ?? '';
        $param['password'] = $param['password'] ?? '';
        $param['confirm_password'] = $param['confirm_password'] ?? '';
        $param['nickname'] = $param['nickname'] ?? '';
        $param['invite_code'] = $param['invite_code'] ?? '';
        $param['verify_code'] = $param['verify_code'] ?? '';
        $param['verify_code_key'] = $param['verify_code_key'] ?? '';
        $res = LoginAction::registerUseUsername($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }



    // 注册短信验证码
    public function smsCodeForRegister(array $param)
    {
        $param['area_code'] = $param['area_code'] ?? '';
        $param['phone'] = $param['phone'] ?? '';
        $res = LoginAction::smsCode($this , 1 , $param);
        if ($res['code'] != 0) {
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
        if ($res['code'] != 0) {
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
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 修改手机号码验证码
    public function smsCodeForPhone(array $param)
    {
        $param['area_code'] = $param['area_code'] ?? '';
        $param['phone'] = $param['phone'] ?? '';
        $res = LoginAction::smsCode($this , 4 , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 随机昵称列表
    public function nickname(array $param)
    {
        $param['limit'] = $param['limit'] ?? '';
        $res = LoginAction::nickname($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 图形验证码
    public function captcha(array $param)
    {
        $res = LoginAction::captcha($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 忘记密码短信验证码
    public function smsCodeForForgetPassword(array $param)
    {
        $param['area_code'] = $param['area_code'] ?? '';
        $param['phone'] = $param['phone'] ?? '';
        $res = LoginAction::smsCode($this , 5 , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 忘记密码-修改密码
    public function forgetPassword(array $param)
    {
        $param['area_code'] = $param['area_code'] ?? '';
        $param['phone'] = $param['phone'] ?? '';
        $param['password'] = $param['password'] ?? '';
        $param['confirm_password'] = $param['confirm_password'] ?? '';
        $res = LoginAction::forgetPassword($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 注册极限验证会话（第一次）
    public function registerValidateSession(array $param)
    {
        $res = LoginAction::registerValidateSession($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }
    

    public function testTwo()
    {


    }

    public function testHttp()
    {
        print_r($_SERVER);
    }

    // 生成二维码数据
    public function loginQRCode(array $param)
    {
        $res = LoginAction::loginQRCode($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 生成二维码数据
    public function loginQRCodeForTest(array $param)
    {
        $res = LoginAction::loginQRCodeForTest($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }
}