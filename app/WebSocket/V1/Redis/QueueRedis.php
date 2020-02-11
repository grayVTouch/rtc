<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/18
 * Time: 10:14
 */

namespace App\WebSocket\V1\Redis;

use Engine\Facade\Redis as RedisFacade;

class QueueRedis extends Redis
{
    public static $key = 'queue';

    // 添加到列表的尾部
    public static function push(string $value)
    {
        return RedisFacade::rPush(self::$key , $value);
    }

    // 添加到列表的首部
    public static function unshift(string $value)
    {
        return RedisFacade::lPush(self::$key , $value);
    }

    // 获取列表第一个元素弹出
    public static function shift()
    {
        return RedisFacade::lPop(self::$key);
    }

    // 获取列表最后一个元素弹出
    public static function pop()
    {
        return RedisFacade::rPop(self::$key);
    }
}