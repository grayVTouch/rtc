<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 11:24
 */

namespace App\WebSocket\V1\Redis;

class UserOptionRedis extends Redis
{
    public static function userOptionByIdentifierAndUserIdAndValue(string $identifier , int $user_id , $value = null)
    {
        $name = sprintf(self::$userOption , $identifier , $user_id);
        if (empty($value)) {
            $res = self::string($name);
            return json_decode($res);
        }
        return self::string($name , json_encode($value));
    }

    public static function delByIdentifierAndUserId(string $identifier , int $user_id)
    {
        $name = sprintf(self::$userOption , $identifier , $user_id);
        return self::del($name);
    }
}