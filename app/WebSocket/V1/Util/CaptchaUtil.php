<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/9
 * Time: 9:57
 */

namespace App\WebSocket\V1\Util;

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;

class CaptchaUtil extends Util
{
    // 验证码长度
    private static $len = 4;

    // 验证码随机数取用范围
    private static $range = 'abcdefghijklmnopqrstuvwxyz0123456789';

    // 验证码有效时间
    private static $duration = 5;

    // 生成图形验证码
    public static function make()
    {
        // 随机数生成函数
        $builder = new PhraseBuilder(self::$len);
        $captcha = new CaptchaBuilder(null, $builder);
        $captcha->build(150 , 40);
        $jpeg = $captcha->get(80);
        $jpeg = sprintf("data:image/jpeg;base64,%s" , base64_encode($jpeg));
        // 图形验证码
        $verify_code = $captcha->getPhrase();
        $expire = date('Y-m-d H:i:s' , time() + self::$duration * 60);
        $key = json_encode([
            'expire'        => $expire ,
            'verify_code'   => $verify_code ,
        ]);
        $key = base64_encode($key);
        return self::success([
            'key'    => $key ,
            'image'  => $jpeg
        ]);
    }

    // 检查图形验证码
    public static function check($verify_code , $key)
    {
        $key = base64_decode($key);
        $key = json_decode($key , true);
        $datetime = date('Y-m-d H:i:s');
        if ($datetime > $key['expire']) {
            return self::error('图形验证码已过期');
        }
        $key['verify_code'] = strtolower($key['verify_code']);
        $verify_code = strtolower($verify_code);
        if ($key['verify_code'] != $verify_code) {
            return self::error('图形验证码错误');
        }
        return self::success();
    }


}