<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/11
 * Time: 17:06
 */

namespace App\WebSocket;

use App\Model\UserToken;
use App\Model\User;
use App\Util\Data;
use App\WebSocket\Action\LoginAction;
use Exception;

class Auth extends Base
{
    public $user;

    public function before() :bool
    {
        if (!parent::before()) {
            return false;
        }
        return $this->auth();
    }

    // 用户认证
    public function auth() :bool
    {
        $token = UserToken::findByToken($this->token);
        if (empty($token)) {
            $this->conn->disconnect($this->fd , 401 , '用户认证失败');
            return false;
        }
        $user = User::findById($token->user_id);
        if (empty($user)) {
            $this->conn->disconnect($this->fd , 404 , '用户不存在');
            return false;
        }
        $this->user = $user;
        return true;
    }



}