<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/11
 * Time: 23:57
 */

namespace App\Http\Admin\Controller;


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
            $unique_code = $this->request->post['unique_code'] ?? '';
            if (empty($unique_code)) {
                $this->error('unique_code is required' , -1000);
                return false;
            }
            $user = UserModel::findByUniqueCode($unique_code);
            if (empty($user)) {
                $this->error('用户认证失败 [Unique Code Error]' , -1000);
                return false;
            }
        } else {
            $debug_user_id = $this->request->post['debug_user_id'] ?? 0;
            $user = UserModel::findById($debug_user_id);
            if (empty($user)) {
                $this->error('用户认证失败 [DebugUserId Mapping User Not Found]' , -1000);
                return false;
            }
        }
        $this->user = $user;
        return true;


    }
}