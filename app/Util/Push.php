<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 10:24
 */

namespace App\Util;


use App\Redis\UserRedis;
use Core\Lib\Container;

class Push
{
    public static function single(string $identifier , int $user_id , string $type = '' , $data = [] , array $exclude = [])
    {
        // 其他格式要求
        $default = [
            'type' => 'type' ,
            'data' => '' ,
        ];
        // 群聊格式要求
        $default = [
            // 群聊消息
            'type'  => 'group_message' ,
            // 参考 xq_group_message 表
            'data'  => []
        ];
        // 私聊格式要求
        $default = [
            // 私聊消息
            'type'  => 'message' ,
            // 参考 xq_message 表
            'data'  => []
        ];
        // 检查是否在线
        if (!UserRedis::isOnline($identifier , $user_id)) {
            var_dump('用户不再线 identifier: ' . $identifier . ' ; user_id: ' . $user_id);
            return false;
        }
        $conns = UserRedis::fdByUserId($identifier , $user_id);
        $websocket = Container::make('websocket');
        $res = [
            'success'   => 0 ,
            'fail'      => 0 ,
        ];
        foreach ($conns as $v)
        {
            if (in_array($v , $exclude)) {
                // 跳过排除的客户端连接
                continue ;
            }
            if (!$websocket->exists($v)) {
                // 连接已经无效，跳过
                $res['fail']++;
                continue ;
            }
            $send = $websocket->push($v , json_encode([
                'type' => $type ,
                'data' => $data ,
            ]));
            if (!$send) {
                // 发送失败
                $res['fail']++;
                continue ;
            }
            // 发送成功
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
}