<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/19
 * Time: 11:10
 */

namespace WebSocket;

use function extra\config as config_function;

function ws_config(string $key , array $args = []){
    $dir = __DIR__ . '/../Config';
    return config_function($dir , $key , $args);
}
