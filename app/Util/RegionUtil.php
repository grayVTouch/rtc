<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/27
 * Time: 14:03
 */

namespace App\Util;


use Core\Lib\Http;

class RegionUtil extends Util
{

    // ip 地址查询库
    private static $ipQuery = 'http://ip.taobao.com/service/getIpInfo.php?ip=';

    // IP 地址查询库
    public static function getByIP(string $ip = '')
    {
        $api = self::$ipQuery . $ip;
        $res = Http::get($api);
        if (empty($res)) {
            return self::error('网络请求失败' , 500);
        }
        $res = json_decode($res , true);
        if ($res['code'] != 0) {
            return self::error("ip 地址查询库远程接口错误：" . $res['data'] , 500);
        }
        return self::success($res['data']);
    }
}