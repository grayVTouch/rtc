<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/2/16
 * Time: 18:52
 */

namespace App\WebSocket\V1\Util;


use App\WebSocket\V1\Model\RedPacketReceiveLogModel;
use App\WebSocket\V1\Redis\RedPacketReceivedLogRedis;

class RedPacketReceiveLogUtil extends Util
{
    public static function handle($red_packet_receive_log , int $user_id = 0)
    {

    }

    public static function getNumberForMostMoneyByUserIdAndYear(int $user_id , string $year)
    {
        $res = RedPacketReceiveLogModel::getByUserIdAndTypeAndYear($user_id , 'group' , $year);
        $number = 0;
        foreach ($res as $v)
        {
            $best_user_id = RedPacketReceiveLogModel::getUserIdForMostMoneyByRedPacketId($v->red_packet_id);
            if ($best_user_id != $user_id) {
                continue ;
            }
            $number++;
        }
        return $number;
    }
}