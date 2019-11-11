<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/12
 * Time: 22:36
 */

use Swoole\WebSocket\Server as WebSocket;

$ws = new WebSocket('0.0.0.0' , 10002);

// 设置异步任务进程
$ws->set([
    // 业务进程
    'worker_num' => 1 ,
    // 任务进程
    'task_worker_num' => 2 ,
]);

$ws->on('task' , function(WebSocket $server , $task_id , $from_id , $data){
    var_dump("task start");
    var_dump('task_id: ' . $task_id);
    var_dump('from_id' . $from_id);
    var_dump('data: ' . $data);
    $server->finish($data);
});

$ws->on('finish' , function(WebSocket $server , $task_id , $data){
    var_dump("finish");
    var_dump('task_id: ' . $task_id);
    var_dump('data: ' . $data);
});

// 接收到消息的时候进行投递任务
$ws->on('message' , function(WebSocket $server , $frame){
    var_dump("接收到的消息：" . $frame->data);
    // 投递任务
    $server->task($frame->data);
});

$ws->start();