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
    private static $ipQuery = 'http://api.map.baidu.com/location/ip?ak=%s&ip=%s&coor=bd09ll';

    private static $baiduAk = 'tQklyQmog4CA9Swqc5a2wlFLhm5CNC12';

    // IP 地址查询库
    public static function getByIP(string $ip = '')
    {
        $api = sprintf(self::$ipQuery , self::$baiduAk , $ip);
        $res = Http::get($api);
        if (empty($res)) {
            return self::error('网络请求失败' , 500);
        }
        $res = json_decode($res , true);
        if ($res['status'] != 0) {
            // 错误码详情查询网站：http://lbsyun.baidu.com/index.php?title=webapi/ip-api
            return self::error("百度 ip 地址查询库远程接口错误，status: " . $res['status'] , 600);
        }
        return self::success([
            'address' => $res['address'] ,
            'content' => $res['content'] ,
        ]);
    }
}