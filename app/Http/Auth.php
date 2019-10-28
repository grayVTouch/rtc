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

    protected $debug = 'running';

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
        $debug = $this->request->post['debug'] ?? '';
        if ($debug != 'running') {
            $authorization = $this->request->header['authorization'] ?? '';
            if (empty($authorization)) {
                $this->error('用户认证失败 [Authorization Error]' , 1000);
                return false;
            }
            $token = UserTokenModel::findByToken($authorization);
            if (empty($token)) {
                $this->error('用户认证失败 [Token Error]' , 1000);
                return false;
            }
            $user = UserModel::findById($token->user_id);
            if (empty($user)) {
                $this->error("用户认证失败 [Token Mapping User Not Found]" , 1000);
                return false;
            }
        } else {
            $debug_user_id = $this->request->post['debug_user_id'] ?? 0;
            $user = UserModel::findById($debug_user_id);
            if (empty($user)) {
                $this->error('用户认证失败 [UserId Mapping User Not Found]' , 1000);
                return false;
            }
        }
        $this->user = $user;
        return true;


    }
}