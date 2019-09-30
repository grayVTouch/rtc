<?php

namespace App\Lib\SMS;

use Core\Lib\Http;

/**
 * Description of Zz253
 *
 * @author Sammy Guergachi <sguergachi at gmail.com>
 */
class Zz253 {

    // 国内短信
    private static $inside = [
        'account' => 'N030064_N5402271' ,
        'password' => '1iEITFpVgj0bf9' ,
    ];


    // 国外短信
    private static $outside = [
        'account' => 'I621031_I0211011' ,
        'password' => '0VyZTvXQ1F4bad' ,
    ];

    // api 接口地址
    private static $api = 'http://smssh1.253.com/msg/send/json';

    // 你好
    public static function send($area_code , $phone, $sms_code) {
        if ($area_code == '86') {
            $info = self::$inside;
        } else {
            $info = self::$outside;
        }
        $data = [
            "account"   => $info['account'] ,
            "password"  => $info['password'],
            "msg"       => "您的动态验证码为" . $sms_code . "请在页面输入完成验证。如非本人操作请忽略。 ",
            "phone"     => $phone,
        ];
        $res = self::post($data);
        if (empty($res)) {
            return self::response('curl 发送失败 或 服务器没有返回任何响应' , 500);
        }
        $res = json_decode($res , true);
        if ($res['code'] != 0) {
            return self::response(json_encode($res) , 500);
        }
        return self::response('' , 200);
    }

    // post 请求
    public static function post(array $data)
    {
        return Http::post(self::$api , [
            'data' => json_encode($data) ,
            'header' => [
                'Content-Type' => 'application/json' ,
            ] ,
        ]);
    }

    public static function response($data = '' , $code = 200)
    {
        return [
            'code' => $code ,
            'data' => $data
        ];
    }
}