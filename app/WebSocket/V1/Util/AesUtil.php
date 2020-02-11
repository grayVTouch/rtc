<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/4
 * Time: 19:42
 *
 * aes 128bit cbc 默认填充方式
 */

namespace App\WebSocket\V1\Util;


class AesUtil
{
    public static $method = 'AES-128-CBC';

    // 填充方式
    public static $padding = OPENSSL_PKCS1_PADDING;

    // aes 加密
    public static function encrypt(string $data , string $key , string $iv)
    {
        $str = openssl_encrypt($data , self::$method , $key, self::$padding, $iv);
        return base64_encode($str);
    }

    // aes 解密
    public static function decrypt(string $data , string $key , string $iv)
    {
        $data = base64_decode($data);
        return openssl_decrypt($data, self::$method , $key , self::$padding , $iv);
    }
}