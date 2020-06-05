<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/16
 * Time: 14:19
 */

namespace App\Http\ApiV1\Redis;


class BlacklistRedis extends Redis
{
    public static function blockedByIdentifierAndUserIdAndBlockUserIdAndValue(string $identifier , int $user_id , int $block_user_id , $value = null)
    {
        $name = sprintf(self::$blacklist , $identifier , $user_id , $block_user_id);
        if (is_null($value)) {
            return self::string($name);
        }
        return self::string($name , $value);
    }

    public static function delByIdentifierAndUserIdAndBlockUserId(string $identifier , int $user_id , int $block_user_id)
    {
        $name = sprintf(self::$blacklist , $identifier , $user_id , $block_user_id);
        return self::del($name);
    }
}