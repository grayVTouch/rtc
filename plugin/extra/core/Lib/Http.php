<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/3/18
 * Time: 14:46
 */

namespace Core\Lib;

use Exception;

class Http {
    /**
     * 默认模拟的头部
     *
     * @var string
     */
    private static $userAgent = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36';

    /**
     * 默认选项
     *
     * @var array
     */
    private static $default = [
        // 请求路径
        'url' => '' ,
        // 发送的数据
        'data' => [] ,
        // 请求方式
        'method' => 'get' ,
        // 请求头
        'header' => [] ,
        // cookie
        'cookie' => '' ,
        // timeout（单位：s）
        'timeout' => 0 ,
        // 端口
        'port' => 80 ,
        // 代理通道
        'proxy_tunnel' => false ,
        // 代理类型
        'proxy_type' => 'http' ,
        // 代理ip
        'proxy' => '127.0.0.1' ,
        // 代理端口
        'proxy_port' => 8888 ,
    ];

    /**
     * 代理类型
     *
     * @var array
     */
    private static $proxyType = ['http' , 'socks4' , 'socks5' , 'socks4a' , 'socks5_hostname'];

    /**
     * 发送 post 请求
     *
     * @param $url
     * @param array $option
     * @return null|string
     * @throws \Exception
     */
    public static function post($url , $option = []): ?string
    {
        return self::ajax(array_merge($option , [
            'url' => $url ,
            'method' => 'post'
        ]));
    }

    /**
     * 发送 get 请求
     *
     * @param string $url
     * @param array $option
     * @return null|string
     * @throws \Exception
     */
    public static function get(string $url , array $option = []): ?string
    {
        return self::ajax(array_merge($option , [
            'url'       => $url ,
            'method'    => 'get'
        ]));
    }

    /**
     * 生成符号 curl 格式的请求头
     *
     * @param array $header
     * @return array
     */
    private static function parseHeader(array $header = []): array
    {
        $res = [];
        foreach ($header as $k => $v)
        {
            $res[] = sprintf('%s: %s' , $k , $v);
        }
        return $res;
    }

    /**
     * 获取代理类型
     *
     * @param  string  $type
     * @return int
     * @throws \Exception
     */
    private static function getProxyType($type): int
    {
        $type = strtolower($type);
        switch ($type)
        {
            case 'http':
                return CURLPROXY_HTTP;
            case 'socks4':
                return CURLPROXY_SOCKS4;
            case 'socks5':
                return CURLPROXY_SOCKS5;
            case 'socks4a':
                return CURLPROXY_SOCKS4A;
            case 'socks5_hostname':
                return CURLPROXY_SOCKS5_HOSTNAME;
            default:
                throw new Exception('不支持的代理类型: ' . $type);
        }
    }

    /**
     * 发送 http 请求
     *
     * @param  array $option
     * @return string|null
     * @throws \Exception
     */
    public static function ajax(array $option = []): ?string
    {
        $option['url']      = $option['url'] ?? self::$default['url'];
        $option['data']     = $option['data'] ?? self::$default['data'];
        $option['method']   = $option['method'] ?? self::$default['method'];
        $option['method']   = strtolower($option['method']);
        $option['header'] = $option['header'] ?? self::$default['header'];
        $option['header'] = self::parseHeader($option['header']);
        // cookie
        $option['cookie'] = $option['cookie'] ?? self::$default['cookie'];
        // 单位：s
        $option['timeout'] = $option['timeout'] ?? self::$default['timeout'];
        // 端口！注意，如果请求的不是 80 端口，请务必设置该端口！
        $option['port'] = $option['port'] ?? self::$default['port'];
        // 是否开启代理通道，默认：false
        $option['proxy_tunnel'] = $option['proxy_tunnel'] ?? self::$default['proxy_tunnel'];
        $option['proxy_type']   = $option['proxy_type'] ?? self::$default['proxy_type'];
        $option['proxy']        = $option['proxy'] ?? self::$default['proxy'];
        $option['proxy_port']   = $option['proxy_port'] ?? self::$default['proxy_port'];

        // curl 配置项
        $curl_option = [
            CURLOPT_RETURNTRANSFER => true ,
            CURLOPT_HEADER => false ,
            CURLOPT_URL => $option['url'] ,
            // 要发送的请求头
            CURLOPT_HTTPHEADER => $option['header'] ,
//            CURLOPT_POST => $option['method'] == 'post' ,
//            CURLOPT_PORT => $option['port'] ,
//            CURLOPT_POSTFIELDS => $option['data'] ,
            // user-agent 必须携带！
            CURLOPT_USERAGENT => self::$userAgent ,
            // 要携带的 cookie，不知道能够坚持多久？？
            CURLOPT_COOKIE => $option['cookie'] ,
            CURLOPT_SSL_VERIFYPEER => false ,
            CURLOPT_FOLLOWLOCATION  => true ,
            CURLOPT_MAXREDIRS  => 3 ,
            CURLOPT_TIMEOUT => $option['timeout'] ,
        ];
        $curl_option[CURLOPT_POST] = $option['method'] == 'post';
        if ($curl_option[CURLOPT_POST]) {
            $curl_option[CURLOPT_POSTFIELDS] = $option['data'];
        }
        if ($option['proxy_tunnel']) {
            // 代理
            $curl_option[CURLOPT_PROXYTYPE] = self::getProxyType($option['proxy_type']);
            $curl_option[CURLOPT_PROXY]     = $option['proxy'];
            $curl_option[CURLOPT_PROXYPORT] = $option['proxy_port'];
        }
        $res = curl_init();
        curl_setopt_array($res , $curl_option);
        $str = curl_exec($res);
        curl_close($res);
        return $str;
    }
}