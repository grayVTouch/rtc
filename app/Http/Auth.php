<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/11
 * Time: 23:57
 */

namespace App\Http;


use App\Model\UserToken;

class Auth extends Base
{
    // 前置操作
    public function before() :bool
    {
        if (!parent::before()) {
            return false;
        }
        $authorization = $this->request->header['authorization'] ?? '';
        if (empty($authorization)) {
            $this->error('用户认证失败' , 401);
            return false;
        }
        $token = UserToken::findByToken($authorization);
        if (empty($token)) {
            $this->error('Token 错误' , 401);
            return false;
        }
        return true;


    }
}