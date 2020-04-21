<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/17
 * Time: 0:00
 */

namespace App\Http\ApiV1\Redis;

use Engine\Facade\Redis as RedisFacade;

class MiscRedis extends Redis
{
    public static function fdMappingIdentifier(int $fd , string $identifier = '')
    {
        $extranet_ip = config('app.extranet_ip');
        $name = sprintf(self::$fdMappingIdentifier , $extranet_ip , $fd);
        if (empty($identifier)) {
            return RedisFacade::string($name);
        }
        return RedisFacade::string($name , $identifier);
    }

    public static function delfdMappingIdentifier(int $fd)
    {
        $extranet_ip = config('app.extranet_ip');
        $name = sprintf(self::$fdMappingIdentifier , $extranet_ip , $fd);
        return RedisFacade::del($name);
    }
}