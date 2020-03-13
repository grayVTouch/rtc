<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/11
 * Time: 23:57
 */

namespace App\Http\WebV1\Controller;


use App\Http\WebV1\Model\UserTokenModel;
use App\Http\WebV1\Model\UserModel;

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
            $token = $this->request->post['token'] ?? '';
            $token = UserTokenModel::findByToken($token);
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
                $this->error('用户认证失败 [DebugUserId Mapping User Not Found]' , 1000);
                return false;
            }
        }
        $this->user = $user;
        return true;


    }
}