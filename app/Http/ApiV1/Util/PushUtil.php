<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 10:24
 */

namespace App\Http\ApiV1\Util;


use App\Http\ApiV1\Redis\UserRedis;
use Core\Lib\Http;
use Engine\Facade\WebSocket;

class PushUtil
{
    // 如果在其他服务器上，那么需要通过 url 进行转发
    private static $forwardUrl = 'http://%s:10010/WebV1/Client/push';

    public static function single(string $identifier , int $user_id , string $type = '' , $data = [] , array $exclude = [])
    {
        // 私聊格式要求
        $default = [
            // 私聊消息
            'type'  => 'message' ,
            // 参考 rtc_message 表
            'data'  => []
        ];
        // 检查是否在线
        if (!UserRedis::isOnline($identifier , $user_id)) {
            return false;
        }
        $send_data = [
            'type' => $type ,
            'data' => $data ,
        ];
        $conns = UserRedis::userIdMappingFd($identifier , $user_id);
        $res = [
            'success'   => 0 ,
            'fail'      => 0 ,
        ];
        // 当前服务器外网 ip
        $extranet_ip = config('app.extranet_ip');
        $conns_for_other_server = [];
        // 转发当前服务器上的客户端，并存储其他服务器上的客户端
        foreach ($conns as $v)
        {
            $is_exclude = false;
            foreach ($exclude as $v1)
            {
                if (
                    $v['extranet_ip'] == $v1['extranet_ip'] &&
                    $v['client_id'] == $v1['client_id']
                ) {
                    $is_exclude = true;
                }
            }
            if ($is_exclude) {
                // 如果是排除的客户端，跳过
                continue ;
            }
            if ($extranet_ip == $v['extranet_ip']) {
                // 当前服务器
                if (!WebSocket::exist($v['client_id'])) {
                    // 连接已经无效，跳过
                    $res['fail']++;
                    continue ;
                }
                $send = WebSocket::push($v['client_id'] , json_encode($send_data));
                if (!$send) {
                    // 发送失败
                    $res['fail']++;
                    continue ;
                }
                // 发送成功
                $res['success']++;
                continue ;
            }
            // 其他服务器上的客户端连接
            if (!isset($conns_for_other_server[$v['extranet_ip']])) {
                $conns_for_other_server[$v['extranet_ip']] = [];
            }
            $conns_for_other_server[$v['extranet_ip']][] = $v;

        }
        // 转发其他服务器上的客户端
        foreach ($conns_for_other_server as $v)
        {
            $send = Http::post(sprintf(self::$forwardUrl , $v['extranet_ip']) , [
                'data' => [
                    // 项目标识符
                    'identifier' => $identifier ,
                    // 待接收的客户端 id 列表
                    'client'    => json_encode($v) ,
                    // 排除的客户端
                    'exclude'   => json_encode($exclude) ,
                    // 要发送的数据
                    'data'      => json_encode($send_data)
                ]
            ]);
            if (empty($send)) {
                $res['fail']++;
                continue ;
            }
            if ($send['code'] != 0) {
                $res['fail']++;
                continue ;
            }
            $res['success']++;
        }
        return $res;
    }

    public static function multiple(string $identifer = '' , array $user_ids = [] , string $type = '' , $data = [] , array $exclude = [])
    {
        $res = [];
        foreach ($user_ids as $v)
        {
            $res[$v] = self::single($identifer , $v , $type , $data , $exclude);
        }
        return $res;
    }

    // 投递任务
    public static function deliveryTask($data)
    {
        return WebSocket::deliveryTask($data);
    }

    // 不是向用户推送，而是向客户端推送
    public static function singleForClient(string $identifier , $client , string $type = '' , $data = [] , $exclude = [])
    {
        foreach ($exclude as $v)
        {
            if ($client['extranet_ip'] == $v['extranet_ip'] && $client['client_id'] == $v['client_id']) {
                // 跳过排除的客户端
                return true;
            }
        }
        $extranet_ip = config('app.extranet_ip');
        if ($extranet_ip == $client['extranet_ip']) {
            // 当前服务器上的客户端id
            if (!WebSocket::exist($client['client_id'])) {
                // 连接已经无效，跳过
                return false;
            }
            $send = WebSocket::push($client['client_id'] , json_encode([
                'type' => $type ,
                'data' => $data ,
            ]));
            if (!$send) {
                return false;
            }
            return true;
        }
        // 其他节点上的客户端连接 id
        $send = Http::post(sprintf(self::$forwardUrl , $client['extranet_ip']) , [
            'data' => [
                // 项目标识符
                'identifier' => $identifier ,
                // 待接收的客户端 id 列表
                'client'    => json_encode([$client]) ,
                // 排除的客户端
                'exclude'   => json_encode($exclude) ,
                // 要发送的数据
                'data'      => json_encode($data)
            ]
        ]);
        if (empty($send)) {
            return false;
        }
        if ($send['code'] != 0) {
            return false;
        }
        return true;
    }

    public static function multipleForClient(string $identifier , array $clients , string $type , $data = [] , $exclude = [])
    {
        $res = [];
        foreach ($clients as $v)
        {
            $res[sprintf('%s_%s' , $v['extranet_ip'] , $v['client_id'])] = self::singleForClient($identifier , $v , $type , $data , $exclude);
        }
        return $res;
    }
}