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
    /**
     * @param string $name
     * @param string $value
     * @param int $expire 默认 2d，单位 s
     * @return mixed
     */
    public static function value(string $name , string $value = '' , int $expire = 172800)
    {
        if (empty($value)) {
            return RedisFacade::string($name , $value);
        }
        return RedisFacade::string($name , $value , $expire);
    }
}