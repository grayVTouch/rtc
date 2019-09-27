<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/8
 * Time: 8:36
 */

namespace App\Http\Util;

use App\Util\PushUtil as BasePushUtil;

class PushUtil extends Util
{
    // 更新未读消息
    public static function refreshUnreadCountForPush(string $identifier , int $user_id)
    {
        return BasePushUtil::single($identifier , $user_id , 'refresh_unread_count_for_push');
    }
}