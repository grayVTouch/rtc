<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/11
 * Time: 17:06
 */

namespace App\WebSocket;

use App\Model\UserToken;
use App\Model\UserModel;
use App\Util\DataUtil;
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
        if ($this->debug != 'running') {
            if (empty($token)) {
//                $this->conn->disconnect($this->fd , 403 , '用户认证失败');
//                $this->conn
                return false;
            }
            $user = UserModel::findById($token->user_id);
            if (empty($user)) {
//                $this->conn->disconnect($this->fd , 403 , '用户不存在');
                return false;
            }
            $this->user = $user;
        } else {
//            var_dump($this->userId);
            // 调试模式！跳过认证直接获取用户数据
            $this->user = UserModel::findById($this->userId);
        }
        return true;
    }



}