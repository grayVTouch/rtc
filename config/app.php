<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/10
 * Time: 16:46
 */

// web 服务器地址
$host = "http://192.168.61.102:9301";
//$host = "http://192.168.1.67:9301";
// 监听的 ip
$ip = '0.0.0.0';
return [
    'websocket' => [
        'ip' => $ip ,
        'port' => 9300 ,
        // 重复使用端口【如果 worker != 1，请务必设置端口重用 = true】
        'reuse_port' => true ,
        // 任务进程的数量
        'task_worker' => 8 ,
        // worker 进程的数量
        'worker' => 8 ,
    ] ,
    'http' => [
        'ip' => $ip ,
        'port' => 9301 ,
    ] ,
    // redis 默认过期时间（1个月）
    'timeout' => 1 * 30 * 24 * 3600 ,
    // 是否启用访客模式
    'enable_guest' => true ,
    // 单个客服最多接听的访客数量
    'number_of_receptions' => 10 ,
    // 客服最长等待时间 2min
    'wait_duration' => 2 * 60 ,
    // 记录数限制
    'limit' => 20 ,
    // 网站路径
    'web_dir' => realpath(__DIR__ . '/../public') ,
    // 日志目录
    'log_dir' => realpath(__DIR__ . '/../log') ,
    // host
    'host' => $host ,
    // 默认头像
    'avatar'        => "{$host}/static/image/avatar.png" ,
    // 默认群头像
    'group_image'   => "{$host}/static/image/group.png" ,

];