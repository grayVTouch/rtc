<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/16
 * Time: 11:38
 */

namespace App\Http\ApiV1\Redis;


class GroupRedis extends Redis
{
    public static function groupByIdentifierAndGroupIdAndValue(string $identifier , int $group_id , $value = null)
    {
        $name = sprintf(self::$group , $identifier , $group_id);
        if (empty($value)) {
            $res = self::string($name);
            return json_decode($res);
        }
        return self::string($name , json_encode($value));
    }

    public static function delByIdentifierAndGroupId(string $identifier , int $group_id)
    {
        $name = sprintf(self::$group , $identifier , $group_id);
        return self::del($name);
    }
}