<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/31
 * Time: 13:40
 */

$redis = new Redis();

$res = $redis->connect('127.0.0.1' , 6379 , 30);
if (!$res) {
    var_dump("redis 连接失败");
}

$redis->auth('364793');

// 使用 集合

$redis->sAdd('user_id_mapping_fd' , 1);
$redis->sAdd('user_id_mapping_fd' , 2);


// 取出数据
$data = $redis->sMembers('fuck');

var_dump($data);