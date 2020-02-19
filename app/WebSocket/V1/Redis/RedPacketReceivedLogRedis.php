<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/16
 * Time: 14:19
 */

namespace App\WebSocket\V1\Redis;

use Engine\Facade\Redis as RedisFacade;

class RedPacketReceivedLogRedis extends Redis
{
    public static function redPacketReceivedLogByIdentifierAndRedPacketIdAndUserIdAndVal(string $identifier , int $red_packet_id , int $user_id , $val = null)
    {
        $name = sprintf(self::$redPacketReceivedLog , $identifier , $red_packet_id , $user_id);
        if (empty($val)) {
            return self::string($name);
        }
        $red_packet_expired_duration = config('app.red_packet_expired_duration');
        return RedisFacade::string($name , $val , $red_packet_expired_duration);
    }

    public static function delByIdentifierAndRedPacketIdAndUserId(string $identifier , int $red_packet_id , int $user_id)
    {
        $name = sprintf(self::$redPacketReceivedLog , $identifier , $red_packet_id , $user_id);
        return self::del($name);
    }
}