<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/27
 * Time: 14:21
 */

namespace App\WebSocket\Action;


use App\Util\RegionUtil;
use App\WebSocket\Auth;

class MiscAction extends Action
{
    public static function outside(Auth $auth , array $param)
    {
        $res = $auth->conn->getClientInfo($auth->fd);
        if (empty($res)) {
            return self::error('获取客户端信息失败' , 500);
        }
        $info = RegionUtil::getByIP($res['remote_ip']);
        if ($info['code'] != 200) {
            return self::error($info['data'] , 500);
        }
        $info = $info['data'];
        if ($info['country_id'] != 'CN') {
            return self::success(1);
        }
        return self::error(0);
    }
}