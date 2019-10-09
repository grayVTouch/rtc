<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/27
 * Time: 14:10
 */

namespace App\Util;


use App\Model\BlacklistModel;
use App\Model\FriendModel;
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
            $user->blocked = BlacklistModel::blocked($relation_user_id, $user->id) ? 1 : 0;
            $user->is_friend = FriendModel::isFriend($relation_user_id , $user->id) ? 1 : 0;
            // 好友名称
            $alias = FriendModel::alias($relation_user_id , $user->id);
            $user->nickname = empty($alias) ? $user->nickname : $alias;
        }
    }
}