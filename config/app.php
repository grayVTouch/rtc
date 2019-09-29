<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/10
 * Time: 16:46
 */

// http 域名
$host = 'http://192.168.145.128';

return [
    // 监听的 ip
    'ip' => '0.0.0.0' ,
    // 监听的端口
    'port' => 10001 ,
    // 重复使用端口【如果 worker != 1，请务必设置端口重用 = true】
    'reuse_port' => true ,
    // 任务进程的数量
    'task_worker' => 0 ,
    // worker 进程的数量
    'worker' => 1 ,
    // 静态文件根目录
    'document_root' => '/myself/command/rtc/public' ,
    // 调试模式
    'debug' => true ,
    // redis 默认过期时间（1个月）
    'timeout' => 1 * 30 * 24 * 3600 ,
    // 是否启用访客模式
    'enable_guest' => false ,
    // 单个客服最多接听的访客数量
    'number_of_receptions' => 10 ,
    // 客服最长等待时间 2min
    'wait_duration' => 5 * 60 ,
    // 记录数限制
    'limit' => 20 ,
    // 网站路径
    'web_dir' => realpath(__DIR__ . '/../public') ,
    // 日志目录
    'log_dir' => realpath(__DIR__ . '/../log') ,
    // host
    'host' => $host ,
    // 默认头像
//    'avatar'        => "{$host}/static/image/avatar.png" ,
    'avatar'        => "" ,
    // 默认群头像
    'group_image'   => "" ,

];