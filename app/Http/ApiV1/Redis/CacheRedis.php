<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/19
 * Time: 14:43
 */

namespace App\Http\ApiV1\Redis;

use Engine\Facade\Redis as RedisFacade;

class CacheRedis extends Redis
{
    public static function value(string $name , string $value = '' , int $expire = 0)
    {
        if (empty($value)) {
            return RedisFacade::string($name , $value);
        }
        return RedisFacade::string($name , $value , $expire);
    }
}