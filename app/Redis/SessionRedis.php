<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/24
 * Time: 11:06
 */

namespace App\Redis;

use Engine\Facade\Redis as RedisFacade;

class SessionRedis extends Redis
{
    public static function sessionMember(string $identifier , string $session_id , $value = null)
    {
        $name = sprintf(self::$sessionMember , $identifier , $session_id);
        if (empty($value)) {
            return (int) RedisFacade::setAll($name);
        }
        return RedisFacade::sAdd($name , $value);
    }

    public static function delSessionMember(string $identifier , string $session_id , $value)
    {
        $name = sprintf(self::$sessionMember , $identifier , $session_id);
        return RedisFacade::sRem($name , $value);
    }

    public static function existSessionMember(string $identifier , string $session_id , $value)
    {
        $name = $name = sprintf(self::$sessionMember , $identifier , $session_id);
        return RedisFacade::sIsMember($name , $value);
    }
}