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
use Illuminate\Database\Capsule\Manager as Capsule;
use App\Application;

$autoload = new Autoload();
$autoload->register([
    'class' => [
        'App\\' => __DIR__ . '/../app' ,
    ] ,
    'file' => [
        // 系统函数
        __DIR__ . '/../common/currency.php'

    ] ,
]);
// 实例化 Laravel Eloquent Database 数据库实例
$database = new Capsule();
$config = config('database.mysql');
$database->addConnection($config);
$database->bootEloquent();

// 跑程序
$app = new Application();
$app->run();