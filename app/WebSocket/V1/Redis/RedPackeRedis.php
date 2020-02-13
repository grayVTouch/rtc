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
    }

    public static function delByIdentifierAndUserIdAndBlockUserId(string $identifier , int $user_id , int $block_user_id)
    {
        $name = sprintf(self::$blacklist , $identifier , $user_id , $block_user_id);
        return self::del($name);
    }
}