<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/9
 * Time: 16:49
 */

require_once __DIR__ . '/../plugin/extra/app.php';
require_once __DIR__ . '/../plugin/database/vendor/autoload.php';

// 注册自动加载
use Core\Lib\Autoload;
use Engine\Application;

$autoload = new Autoload();
$autoload->register([
    'class' => [
        'App\\' => __DIR__ . '/../app' ,
        'Engine\\' => __DIR__ . '/../engine' ,
    ] ,
    'file' => [
        // 系统函数
        __DIR__ . '/../common/currency.php'

    ] ,
]);

// 跑程序
$app = new Application();
$app->run();