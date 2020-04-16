<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/2/16
 * Time: 18:52
 */

namespace App\WebSocket\V1\Util;


use App\WebSocket\V1\Model\RedPacketModel;
use App\WebSocket\V1\Model\RedPacketReceiveLogModel;
use App\WebSocket\V1\Redis\RedPacketReceivedLogRedis;

class RedPacketUtil extends Util
{
    public static function handle(RedPacketModel $red_packet , int $user_id = 0)
    {
        // 多少分钟被抢光
        $red_packet->received_duration = 0;
        if ($red_packet->is_received > 0) {
            // 如果红包被抢光，那么计算从发红包 到 红包被抢光经过了多长时间
            $red_packet->received_duration = strtotime($red_packet->received_time) - strtotime($red_packet->create_time);
        }
        // 是否领取过该红包
        if ($red_packet->type == 'private') {
            // 私聊
            if (empty($user_id) || $red_packet->receiver != $user_id) {
                $red_packet->is_received_for_myself = 0;
            } else {
                $red_packet->is_received_for_myself = $red_packet->is_received;
            }
        } else {
            // 群聊
            $red_packet->is_received_for_myself = RedPacketReceivedLogRedis::redPacketReceivedLogByIdentifierAndRedPacketIdAndUserIdAndVal($red_packet->identifier , $red_packet->id , $user_id);
            $red_packet->is_received_for_myself = $red_packet->is_received_for_myself ?? intval(RedPacketReceiveLogModel::isReceivedByUserIdAndRedPacketId($user_id , $red_packet->id));

        }
    }
}