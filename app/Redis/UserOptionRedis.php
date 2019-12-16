<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 11:24
 */

namespace App\Redis;

use Engine\Facade\Redis as RedisFacade;

class UserOptionRedis extends Redis
{
    public static function userOption(string $identifier , int $user_id , string $value = null)
    {
        $name = sprintf(self::$user , $identifier , $user_id);
        if (empty($value)) {
            return RedisFacade::string($name);
        }
        return RedisFacade::string($name , $value , config('app.cache_duration'));
    }

    public static function delUserOption(string $identifier , int $user_id)
    {
        $name = sprintf(self::$user , $identifier , $user_id);
        return RedisFacade::del($name);
    }
}