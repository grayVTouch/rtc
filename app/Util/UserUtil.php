<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/27
 * Time: 14:10
 */

namespace App\Util;


use App\Model\BlacklistModel;
use App\Redis\UserRedis;

class UserUtil extends Util
{
    /**
     * 处理用户信息
     *
     * @param \App\Model\UserModel|\StdClass $user
     */
    public static function handle($user , int $relation_user_id = 0)
    {
        if (empty($user)) {
            return ;
        }
        $user->online = UserRedis::isOnline($user->identifier , $user->id) ? 1 : 0;
        if (!empty($relation_user_id)) {
            // 黑名单
            $user->blocked = BlacklistModel::blocked($relation_user_id , $user->id);
        }
    }
}