<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/27
 * Time: 14:10
 */

namespace App\Util;


use App\Model\UserModel;

class UserUtil extends Util
{
    // 是否在线
    public static function isOnline()
    {

    }

    // 获取用户信息
    public static function user(int $user_id)
    {
        return UserModel::findById($user_id);
    }
}