<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/10
 * Time: 16:46
 */

return [
    'websocket' => [
        'ip' => '0.0.0.0' ,
        'port' => 9300 ,
        // 重复使用端口【如果 worker != 1，请务必设置端口重用 = true】
        'reuse_port' => true ,
        // 任务进程的数量
        'task_worker' => 8 ,
        // worker 进程的数量
        'worker' => 8 ,
    ] ,
    // redis 默认过期时间（1个月）
    'timeout' => 1 * 30 * 24 * 3600 ,
    // 是否启用访客模式
    'enable_guest' => true ,
    // 单个客服最多接听的访客数量
    'number_of_receptions' => 10 ,
];