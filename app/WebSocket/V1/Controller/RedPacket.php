<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/2/12
 * Time: 11:03
 */

namespace App\WebSocket\V1\Controller;


use App\WebSocket\V1\Action\RedPacketAction;

class RedPacket extends Auth
{
    public function createRedPacketForPrivate(array $param)
    {
        $param['pay_password'] = $param['pay_password'] ?? '';
        $param['money'] = $param['money'] ?? '';
        $param['other_id'] = $param['other_id'] ?? '';
        $res = RedPacketAction::createRedPacketForPrivate($this , $param);
        if ($res['code'] != 0) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    public function receiveRedPacketForPrivate(array $param)
    {
        $param['red_packet_id'] = $param['red_packet_id'] ?? '';
        $res = RedPacketAction::receiveRedPacketForPrivate($this , $param);
        if ($res['code'] != 0) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    public function createRedPacketForGroup(array $param)
    {
        $param['pay_password'] = $param['pay_password'] ?? '';
        $param['money']     = $param['money'] ?? '';
        $param['group_id']  = $param['group_id'] ?? '';
        $param['type']      = $param['type'] ?? '';
        $res = RedPacketAction::createRedPacketForGroup($this , $param);
        if ($res['code'] != 0) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    public function receiveRedPacketForGroup(array $param)
    {
        $param['red_packet_id'] = $param['red_packet_id'] ?? '';
        $res = RedPacketAction::receiveRedPacketForGroup($this , $param);
        if ($res['code'] != 0) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }
}