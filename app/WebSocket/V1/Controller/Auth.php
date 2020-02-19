<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/11
 * Time: 17:06
 */

namespace App\WebSocket\V1\Controller;

use App\WebSocket\V1\Model\UserTokenModel;
use App\WebSocket\V1\Model\UserModel;
use App\WebSocket\V1\Redis\UserRedis;
use App\WebSocket\V1\Util\DataUtil;
use App\WebSocket\V1\Util\UserActivityLogUtil;
use App\WebSocket\V1\Util\UserUtil;
use App\WebSocket\V1\Action\LoginAction;
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
        $token = UserTokenModel::findByToken($this->token);
        if ($this->debug != 'running') {
            if (empty($token)) {
                $this->error('用户认证失败【Empty Token】' , 1000);
//                $this->conn
                return false;
            }
            $user = UserModel::findById($token->user_id);
            if (empty($user)) {
                $this->error('用户认证失败【Token Mapping User Not Found】' , 1000);
                return false;
            }
            $this->user = $user;
        } else {
            // 调试模式！跳过认证直接获取用户数据
            $this->user = UserModel::findById($this->userId);
            if (empty($this->user)) {
                $this->error('用户认证失败【User Not Found】' , 1000);
                return false;
            }
        }
//        var_dump("user_id: " . $this->user->id . ' 正在调用 api');
        UserUtil::handle($this->user);
        // 建立映射
        // 检查该用户是否在线
        $online = UserRedis::isOnline($this->identifier , $this->user->id);
        if (!$online) {
            // 如果之前不在线
            UserActivityLogUtil::createOrUpdateCountByIdentifierAndUserIdAndDateAndData($this->identifier , $this->user->id , date('Y-m-d') , [
                'online_count' => 'dec'
            ]);
        }
        UserRedis::fdMappingUserId($this->identifier , $this->fd , $this->user->id);
        UserRedis::userIdMappingFd($this->identifier , $this->user->id , $this->fd);
        return true;
    }
}