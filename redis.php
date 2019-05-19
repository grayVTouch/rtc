<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/16
 * Time: 15:36
 */

$redis = new Redis();
$res = $redis->connect('127.0.0.1' , '6379' , 0);
if (!$res) {
    throw new Exception('连接失败');
}
$redis->auth('364793');

$redis->hSet('obj' , 'name' , 'grayVTouch');
$redis->hSet('obj' , 'sex' , 'male');

$res = $redis->hGetAll('obj');
print_r($res);
var_dump($res);
