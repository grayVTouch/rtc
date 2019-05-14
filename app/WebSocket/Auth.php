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
use App\Redis\UserRedis;
use App\Util\Data;
use App\WebSocket\Action\LoginAction;
use Exception;

class Auth extends Base
{
    public function before() :bool
    {
        return $this->auth();
    }

    // 用户认证
    public function auth() :bool
    {
        $token = UserToken::findByToken($this->token);
        if (empty($token)) {
            if (!config('app.enable_guest')) {
                $this->conn->disconnect($this->fd , 401 , '用户认证失败');
                return false;
            }
            /**
             * *****************
             * 启用访客模式
             * *****************
             */
            // 创建临时用户
            $user = User::temp($this->identifier);
            if (empty($user)) {
                $this->conn->disconnect($this->fd , 500 , '创建访客账号失败');
                return false;
            }
            // 自动登录
            $res = LoginAction::login([
                'unique_code' => $user->unique_code
            ]);
            if ($res['code'] != 200) {
                $this->conn->disconnect($this->fd , 500 , '访客自动登录失败，失败信息：' . json_encode($res['data']));
                return false;
            }
            $this->token = $res['data'];
            // 通知客户端更新登录信息
            $this->push($this->fd , 'token' , Data::token($this->token));
            // 绑定 user_id <=> fd
            $res = UserRedis::bindFdByUserId($this->fd , $user->identifier , $user->user_id);
            if ($res == false) {
                $this->conn->disconnect($this->fd , 500 , 'Redis 服务器挂了');
                return false;
            }
            return true;
        }
        $user = User::findById($token->user_id);
        if (empty($user)) {
            $this->conn->disconnect($this->fd , 404 , '用户不存在');
            return false;
        }
        $res = UserRedis::bindFdByUserId($this->fd , $user->identifier , $user->user_id);
        if ($res == false) {
            $this->conn->disconnect($this->fd , 500 , 'Redis 服务器挂了');
            return false;
        }
        return true;
    }



}