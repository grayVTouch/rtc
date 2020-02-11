<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/27
 * Time: 14:21
 */

namespace App\WebSocket\V1\Action;


use App\WebSocket\V1\Util\RegionUtil;
use App\WebSocket\V1\Controller\Auth;

class MiscAction extends Action
{
    public static function outside(Auth $auth , array $param)
    {
        $res = $auth->conn->getClientInfo($auth->fd);
        if (empty($res)) {
            return self::error('获取客户端信息失败' , 500);
        }
        $info = RegionUtil::getByIPUseBaiduMap($res['remote_ip']);
        if ($info['code'] != 0) {
            // 改用腾讯地图 api
            $info = RegionUtil::getByIPUseQQMap($res['remote_ip']);
            if ($info['code'] != 0) {
                return self::success(1);
            }
            if ($info['address'] == 110000) {
                // 在中国
                return self::success(0);
            }
            return self::success(1);
        }
        return self::success(0);
    }
}