<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/11
 * Time: 23:57
 */

namespace App\Http;


use App\Model\UserTokenModel;
use App\Model\UserModel;

class Auth extends Base
{
    /**
     * @var UserModel
     */
    public $user = null;

    /**
     * 前置操作
     *
     * @return bool
     */
    public function before() :bool
    {
        if (!parent::before()) {
            return false;
        }
        $authorization = $this->request->header['authorization'] ?? '';
        if (empty($authorization)) {
            $this->error('用户认证失败' , 403);
            return false;
        }
        $token = UserTokenModel::findByToken($authorization);
        if (empty($token)) {
            $this->error('Token 错误' , 403);
            return false;
        }
        $user = UserModel::findById($token->user_id);
        if (empty($user)) {
            $this->error("未找到 {$token->user_id} 对应用户");
            return false;
        }
        $this->user = $user;
        return true;


    }
}