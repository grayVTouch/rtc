<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/2/12
 * Time: 11:03
 */

namespace App\WebSocket\V1\Controller;


use App\WebSocket\V1\Action\RedPacketAction;
use App\WebSocket\V1\Model\TestUserModel;
use Illuminate\Support\Facades\DB;

class RedPacket extends Auth
{
    public function getBalance(array $param)
    {
        $res = RedPacketAction::userBalance($this , $param);
        if ($res['code'] != 0) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    public function myCoin(array $param)
    {
        $res = RedPacketAction::myCoin($this , $param);
        if ($res['code'] != 0) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    public function createRedPacketForPrivate(array $param)
    {
        $param['pay_password'] = $param['pay_password'] ?? '';
        $param['money'] = $param['money'] ?? '';
        $param['other_id'] = $param['other_id'] ?? '';
        $param['remark'] = $param['remark'] ?? '';
        $param['coin_id'] = $param['coin_id'] ?? '';
        $param['coin_ico'] = $param['coin_ico'] ?? '';
        $param['coin_name'] = $param['coin_name'] ?? '';
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
        $param['number']      = $param['number'] ?? '';
        $param['remark'] = $param['remark'] ?? '';
        $param['coin_id'] = $param['coin_id'] ?? '';
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

    // 红包领取记录
    public function redPacketReceivedLog(array $param)
    {
        $param['red_packet_id'] = $param['red_packet_id'] ?? '';
        $param['limit'] = $param['limit'] ?? '';
        $param['limit_id'] = $param['limit_id'] ?? '';
        $res = RedPacketAction::redPacketReceivedLog($this , $param);
        if ($res['code'] != 0) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 统计信息：红包领取记录
    public function redPacketReceivedInfo(array $param)
    {
        $param['year'] = $param['year'] ?? '';
        $res = RedPacketAction::redPacketReceivedInfo($this , $param);
        if ($res['code'] != 0) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 统计信息：红包发送记录
    public function redPacketSendInfo(array $param)
    {
        $param['year'] = $param['year'] ?? '';
        $param['coin_id'] = $param['coin_id'] ?? '';
        $res = RedPacketAction::redPacketSendInfo($this , $param);
        if ($res['code'] != 0) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 总计：红包领取记录
    public function redPacketReceivedLogs(array $param)
    {
        $param['year'] = $param['year'] ?? '';
        $param['coin_id'] = $param['coin_id'] ?? '';
        $param['limit'] = $param['limit'] ?? '';
        $param['limit_id'] = $param['limit_id'] ?? '';
        $res = RedPacketAction::redPacketReceivedLogs($this , $param);
        if ($res['code'] != 0) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 总计：红包发送记录
    public function redPacketSendLogs(array $param)
    {
        $param['year'] = $param['year'] ?? '';
        $param['limit'] = $param['limit'] ?? '';
        $param['limit_id'] = $param['limit_id'] ?? '';
        $res = RedPacketAction::redPacketSendLogs($this , $param);
        if ($res['code'] != 0) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 红包相关限制信息
    public function redPacketLimit(array $param)
    {
        $res = RedPacketAction::redPacketLimit($this , $param);
        if ($res['code'] != 0) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }
}