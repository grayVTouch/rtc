<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/24
 * Time: 11:06
 */

namespace App\Http\ApiV1\Redis;

use Engine\Facade\Redis as RedisFacade;

class SessionRedis extends Redis
{
    public static function sessionMember(string $identifier , string $session_id , $user_id = null)
    {
        $key = sprintf(self::$sessionMember , $identifier , $session_id);
        if (empty($user_id)) {
            return (int) RedisFacade::setAll($key);
        }
        return RedisFacade::sAdd($key , $user_id);
    }

    public static function delSessionMember(string $identifier , string $session_id , $user_id)
    {
        $key = sprintf(self::$sessionMember , $identifier , $session_id);
        return RedisFacade::sRem($key , $user_id);
    }

    public static function existSessionMember(string $identifier , string $session_id , $user_id)
    {
        $key = sprintf(self::$sessionMember , $identifier , $session_id);
        return RedisFacade::sIsMember($key , $user_id);
    }
}