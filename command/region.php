<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/2/11
 * Time: 14:11
 */

require_once __DIR__ . '/../plugin/extra/app.php';

use Core\Lib\Http;

class RegionUtil
{
    // 百度地图
    private static $baidu = [
        'query' => 'http://api.map.baidu.com/location/ip?ak=%s&ip=%s&coor=bd09ll' ,
        'secret' => 'tQklyQmog4CA9Swqc5a2wlFLhm5CNC12' ,
    ];

    // 腾讯地图
    private static $qq = [
        'query' => 'https://apis.map.qq.com/ws/location/v1/ip' ,
        'secret' => 'JU6BZ-55L36-Z4ESJ-MYGND-SZQQK-ZZFMS' ,
    ];

    // IP 地址查询库
    public static function getByIPUseBaiduMap(string $ip = '')
    {
        $api = sprintf(self::$baidu['query'] , self::$baidu['secret'] , $ip);
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

    public static function getByIpUseQQMap(string $ip = '')
    {
        $api = self::$qq['query'] . '?' . urlencode(sprintf('key=%s&ip=%s' ,  self::$qq['secret'] , $ip));;
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
            // 中国的国家 code 110000
            'address' => $res['result']['ad_info']['adcode'] ,
            // 国家的中文名称
            'content' => $res['result']['ad_info']['nation'] ,
        ]);
    }

    public static function success($data = '' , int $code = 0)
    {
        return compact('code' , 'data');
    }

    public static function error($data = '' , int $code = 400)
    {
        return compact('code' , 'data');
    }
}

print_r(RegionUtil::getByIPUseBaiduMap('47.241.15.104'));
print_r(RegionUtil::getByIPUseQQMap('47.241.15.104'));