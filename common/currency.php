<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/9
 * Time: 17:15
 */

use function extra\config as config_function;

function config($key , array $args = []){
    $dir = __DIR__ . '/../config';
    return config_function($dir , $key , $args);
}