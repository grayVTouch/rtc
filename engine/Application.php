<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/9
 * Time: 17:29
 */

namespace Engine;

use Core\Lib\Redis;
use Exception;
use Illuminate\Database\Capsule\Manager as Capsule;

use Core\Lib\Container;
use Engine\WebSocket\Connection;

class Application
{
    protected $connection = null;

    public function __construct()
    {
        Container::bind('app' , $this);
    }

    private function initWebSocket()
    {
        $this->connection = new Connection($this);
        Container::bind('connection' , $this->connection);
    }

    // 在 worker 进程启动后执行（必须！！
    // 因为每个主进程创建的数据库连接无法共享给 worker
    // 所以需要每个 wokrer 各自创建一个数据库连接
    public function initDatabase()
    {
        // 实例化 Laravel Eloquent Database 数据库实例
        $database   = new Capsule();
        $config     = config('database.mysql');
        $database->addConnection($config);
        $database->bootEloquent();
        Container::bind('database' , $database);
    }

    // 初始化 Redis
    public function initRedis()
    {
        $config = config('database.redis');
        $redis = new Redis($config);
        Container::bind('redis' , $redis);
    }

    public function run()
    {
        $this->initWebSocket();
    }
}