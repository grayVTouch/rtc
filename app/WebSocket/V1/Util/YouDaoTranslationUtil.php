<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/4
 * Time: 17:11
 */

namespace App\WebSocket\V1\Util;

use function core\array_to_obj;
use function core\convert_obj;
use function core\obj_to_array;
use function core\ssl_random;
use Core\Lib\Http;
use Exception;
use function extra\has_cn;
use function extra\is_http;
use Traversable;

class YouDaoTranslationUtil extends Util
{
    protected static $apiUrl = 'https://openapi.youdao.com/api';
    protected static $appKey = '4b85d34fbd303817';
    protected static $appSecret = 'HmpbLnhsaVWn1DYRV6p6NbhF8FOCSx4m';
    protected static $signType = 'v3';
    protected static $timeout = 3;

    // 生成盐值
    protected static function salt()
    {
        return ssl_random(256);
    }

    // 生成 input
    protected static function input($str = '')
    {
        $len = mb_strlen($str);
        if ($len <= 20) {
            return $str;
        }
        $prefix = mb_substr($str , 0, 10);
        $suffix = mb_substr($str , $len - 10 , 10);
        return $prefix . $len . $suffix;
    }

    // 生成签名
    protected static function sign($str , $salt , $time)
    {
        $input = self::input($str);
        return hash('sha256' , self::$appKey . $input . $salt . $time . self::$appSecret);
    }

    /**
     * 支持的语言包列表
     *
     *  中文	zh-CHS
        英文	en
        日文	ja
        韩文	ko
        法文	fr
        西班牙文	es
        葡萄牙文	pt
        意大利文	it
        俄文	ru
        越南文	vi
        德文	de
        阿拉伯文	ar
        印尼文	id
        自动识别	auto
     */
    public static function translate($str = '' , string $source = 'zh-CHS' , string $target = 'en')
    {
        if ($source == $target) {
            return $str;
        }
        $salt = self::salt();
        $time = time();
        $sign = self::sign($str , $salt , $time);
        $post_data = [
            'q'         => $str ,
            'from'      => $source ,
            'to'        => $target ,
            'appKey'    => self::$appKey ,
            'salt'      => $salt ,
            'sign'      => $sign ,
            'signType'  => self::$signType ,
            'curtime'   => $time ,
        ];
        $res = Http::post(self::$apiUrl , [
            'data'      => $post_data ,
            'timeout'   => self::$timeout
        ]);
        $res = json_decode($res , true);
        if ($res['errorCode'] != 0) {
            // 翻译失败
            return '';
        }
        return empty($res['translation']) ?
            '' :
            ($res['translation'][0] ?? '');
    }
}