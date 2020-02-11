<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/2/11
 * Time: 10:06
 */


require_once __DIR__ . '/../plugin/extra/app.php';
require_once __DIR__ . '/../plugin/database/vendor/autoload.php';

// 注册自动加载
use Core\Lib\Autoload;
use Illuminate\Support\Facades\Facade;
use Illuminate\Database\Capsule\Manager as Capsule;

$autoload = new Autoload();
$autoload->register([
    'class' => [
        'Command\\' => __DIR__ ,
    ] ,
    'file' => [
        // 系统函数
        __DIR__ . '/../common/currency.php'

    ] ,
]);

// 实例化 Laravel Eloquent Database 数据库实例
$database   = new Capsule();
$config     = [
    'driver'    => 'mysql',
    'host'      => '47.88.223.82',
//    'host'      => '47.241.15.104',
    'database'  => 'rtc',
//    'database'  => 'rtc_test',
    'username'  => 'root',
    'password'  => '364793',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => 'rtc_',
];
$database->addConnection($config);
$database->bootEloquent();
// 使其支持门面的调用方式
// 必须使用 Laravel 的门面
// 因为 DB::class 门面类继承的使
// Laravel 的 Facade
Facade::setFacadeApplication([
    'db' =>$database->getDatabaseManager() ,
]);