<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/17
 * Time: 0:00
 */

namespace App\Redis;


class MiscRedis extends Redis
{
    public static function fdMappingIdentifier($fd , $identifier = '')
    {
        $name = sprintf(self::$fdMappingIdentifier , $fd);
        if (empty($identifier)) {
            return redis()->string($name);
        }
        return redis()->string($name , $identifier);
    }

    public static function delfdMappingIdentifier($fd)
    {
        $name = sprintf(self::$fdMappingIdentifier , $fd);
        return redis()->del($name);
    }
}