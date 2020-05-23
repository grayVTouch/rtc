<?php

namespace App\WebSocket\V1\Api;

use Core\Lib\Http;

/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/4/20
 * Time: 14:01
 */

class Api
{
    public static $hostForShop = "http://161.117.233.100/";

    public const SUCCESS_CODE = 0;

    // 生成商城远程接口地址
    public static function genApiByPathInShop($path = '')
    {
        $host = rtrim(self::$hostForShop , '/');
        $path = ltrim($path , '/');
        return $host . '/' . $path;
    }

    public static function success($data = '' , $code = 0)
    {
        return self::response($data , $code);
    }

    public static function error($data = '' , $code = 400)
    {
        return self::response($data , $code);
    }

    public static function response($data , $code)
    {
        return [
            'code' => $code ,
            'data' => $data ,
        ];
    }

    // 发起请求
    public static function post($api , $data)
    {
        $res = Http::post($api , [
            'data' => $data
        ]);
        if (empty($res)) {
            return self::error('网络请求错误 或 服务器没有任何响应');
        }
        $res = json_decode($res , true);
        var_dump($res);
        if (empty($res)) {
            return self::error('服务器没有任何响应');
        }
        if ($res['code'] != self::SUCCESS_CODE) {
            // 发生错误
            return self::error('远程接口错误: code: ' . $res['code'] . '; data: ' . $res['data'] , 500);
        }
        return self::success($res['data']);
    }

}