<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/27
 * Time: 14:10
 */

namespace App\Util;


use App\Redis\UserRedis;

class UserUtil extends Util
{
    /**
     * 处理用户信息
     *
     * @param \App\Model\UserModel|\StdClass $user
     */
    public static function handle($user)
    {
        if (empty($user)) {
            return ;
        }
        $user->online = UserRedis::isOnline($user->identifier , $user->id) ? 1 : 0;
    }
}