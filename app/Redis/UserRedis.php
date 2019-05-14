<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/11
 * Time: 17:43
 */

namespace App\Redis;

class UserRedis extends Redis
{
    public static function bindFdByUserId($fd , $identifier , $user_id)
    {
        $name = sprintf(self::$fdKey , $identifier , $user_id);
        $value = self::findFdByUserId($identifier , $user_id);
        if (empty($value)) {
            $value = [$fd];
        } else {
            array_splice($value , array_search($fd , $value) , 1);
            $value[] = $fd;
        }
        $value = json_encode($value);
        // 注意我们这个允许多端登录！！
        return redis()->string($name , $value , config('app.timeout'));
    }

    public static function findFdByUserId($identifier , $user_id)
    {
        $name = sprintf(self::$fdKey , $identifier , $user_id);
        $res = redis()->string($name);
        return json_decode($res , true);
    }

    public static function isOnline($identifier , $user_id)
    {
        return !empty(self::findFdByUserId($identifier , $user_id));
    }

    // 客服负载均衡
    public static function findServiceLoader()
    {

    }
}