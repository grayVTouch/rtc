<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/17
 * Time: 0:00
 */

namespace App\WebSocket\V1\Redis;

use Engine\Facade\Redis as RedisFacade;

class MiscRedis extends Redis
{
    public static function fdMappingIdentifier(int $fd , string $identifier = '')
    {
        $name = sprintf(self::$fdMappingIdentifier , $fd);
        if (empty($identifier)) {
            return RedisFacade::string($name);
        }
        return RedisFacade::string($name , $identifier);
    }

    public static function delfdMappingIdentifier(int $fd)
    {
        $name = sprintf(self::$fdMappingIdentifier , $fd);
        return RedisFacade::del($name);
    }
}