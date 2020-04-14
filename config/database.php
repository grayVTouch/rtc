<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/9
 * Time: 17:19
 */

return [
    'mysql' => [
        'driver'    => 'mysql',
        'host'      => '127.0.0.1',
        'database'  => 'rtc_nesm',
        'username'  => 'root',
        'password'  => '364793',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => 'rtc_',
    ] ,

    'redis' => [
        'host'        => '127.0.0.1' ,
        'port'      => 6379 ,
        'password'  => '364793' ,
        'prefix'    => 'nesm_' ,
        'timeout'   => 0 ,
    ] ,
];