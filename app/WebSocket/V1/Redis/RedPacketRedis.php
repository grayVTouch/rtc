<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/16
 * Time: 14:19
 */

namespace App\WebSocket\V1\Redis;

use Engine\Facade\Redis as RedisFacade;

class RedPacketRedis extends Redis
{
    public static function redPacketByIdentifierAndRedPacketIdAndList(string $identifier , int $red_packet_id , array $list)
    {
        $name = sprintf(self::$redPacket , $identifier , $red_packet_id);
        $args = $list;
        array_unshift($args , $name);
        call_user_func_array([RedisFacade::class , 'rPush'] , $args);
        $red_packet_expired_duration = config('app.red_packet_expired_duration');
        RedisFacade::expire($name , $red_packet_expired_duration);
    }

    public static function popByIdentifierAndRedPacketId(string $identifier , int $red_packet_id , string $type = 'left')
    {
        $name = sprintf(self::$redPacket , $identifier , $red_packet_id);
        $type_range = ['left' , 'right'];
        if (!in_array($type , $type_range)) {
            return false;
        }
        if ($type == 'left') {
            return RedisFacade::lPop($name);
        }
        return RedisFacade::rPop($name);
    }

    public static function pushByIdentifierAndRedPacketIdAndValAndType(string $identifier , int $red_packet_id , $val , string $type = 'left')
    {
        $name = sprintf(self::$redPacket , $identifier , $red_packet_id);
        $type_range = ['left' , 'right'];
        if (!in_array($type , $type_range)) {
            return false;
        }
        if ($type == 'left') {
            return RedisFacade::lPush($name , $val);
        }
        return RedisFacade::rPush($name , $val);
    }

    public static function delByIdentifierAndRedPacketId(string $identifier , int $red_packet_id)
    {
        $name = sprintf(self::$redPacket , $identifier , $red_packet_id);
        return self::del($name);
    }
}