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

class BaiduTranslationUtil extends Util
{
    protected static $apiUrl = 'https://fanyi-api.baidu.com/api/trans/vip/translate';
    protected static $appId = '20200313000397655';
    protected static $appKey = 'LzoQFjLUsST_mduSx7q6';

//    protected static $appSecret = '9yGL4GFC7TaRIwhXskKmSu3Hsy7d6zer';
    protected static $signType = 'v3';
    protected static $timeout = 3;

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

    //加密
    protected static function buildSign($query, $appID, $salt, $secKey)
    {
        $str = $appID . $query . $salt . $secKey;
        $ret = md5($str);
        return $ret;
    }


    protected static function call($url, $args=null, $method="post", $testflag = 0, $timeout = 0, $headers=array())
    {
        $ret = false;
        $i = 0;
        while($ret === false)
        {
            if($i > 1)
                break;
            if($i > 0)
            {
                sleep(1);
            }
            $ret = self::callOnce($url, $args, $method, false, $timeout, $headers);
            $i++;
        }
        return $ret;
    }


    protected static function callOnce($url, $args=null, $method="post", $withCookie = false, $timeout = 0, $headers=array())
    {
        $ch = curl_init();
        if($method == "post")
        {
            $data = self::convert($args);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        else
        {
            $data = self::convert($args);
            if($data)
            {
                if(stripos($url, "?") > 0)
                {
                    $url .= "&$data";
                }
                else
                {
                    $url .= "?$data";
                }
            }
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if(!empty($headers))
        {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if($withCookie)
        {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $_COOKIE);
        }
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }


    protected static function convert(&$args)
    {
        $data = '';
        if (is_array($args))
        {
            foreach ($args as $key=>$val)
            {
                if (is_array($val))
                {
                    foreach ($val as $k=>$v)
                    {
                        $data .= $key.'['.$k.']='.rawurlencode($v).'&';
                    }
                }
                else
                {
                    $data .="$key=".rawurlencode($val)."&";
                }
            }
            return trim($data, "&");
        }
        return $args;
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
    public static function translate($str = '' , string $source = 'zh' , string $target = 'en')
    {
        if ($source == $target) {
            return $str;
        }
        $salt = rand(10000,99999);
        $time = time();
        $post_data = [
            'q'         => $str ,
            'from'      => $source ,
            'to'        => $target ,
            'appKey'    => self::$appKey ,
            'salt'      => $salt ,
            'appid' => self::$appId ,
            'sign'  => self::buildSign($str , self::$appId , $salt , self::$appKey) ,
        ];

        $res = self::call(self::$apiUrl , $post_data);
        $res = json_decode($res , true);
        if (empty($res)) {
            return '';
        }
        if (!isset($res['trans_result'])) {
            // 翻译失败
            return '';
        }
        if (count($res['trans_result']) < 1) {
            // 没有任何数据
            return '';
        }
        $res = array_values($res['trans_result']);
        $res = $res[0];
        $res['src'] = $res['src'] ?? '';
        $res['dst'] = $res['dst'] ?? '';
        return $res['dst'];
    }
}