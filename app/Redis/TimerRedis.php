<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/12
 * Time: 10:29
 */

namespace App\Redis;

use Engine\Facade\Redis as RedisFacade;

class TimerRedis extends Redis
{

    public static function onceForClearTmpGroupTimer(string $value = '')
    {
        $name = sprintf(self::$onceForClearTmpGroupTimer);
        if (empty($value)) {
            return RedisFacade::string($name);
        }
        return RedisFacade::string($name , $value);
    }

    public static function delGroupBindWaiter(string $identifier , int $group_id)
    {
        $name = sprintf(self::$groupActiveWaiter , $identifier , $group_id);
        return RedisFacade::del($name);
    }
}