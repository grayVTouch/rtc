<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/19
 * Time: 14:43
 */

namespace App\WebSocket\V1\Redis;

use Engine\Facade\Redis as RedisFacade;

class CacheRedis extends Redis
{
    public static function value(string $name , string $value = '' , int $expire = 172800)
    {
        if (empty($value)) {
            return RedisFacade::string($name);
        }
        return RedisFacade::string($name , $value , $expire);
    }
}