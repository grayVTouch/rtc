<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/9
 * Time: 17:29
 */

namespace Engine;

use Exception;

use ArrayAccess;
use Core\Lib\Redis;
use Core\Lib\Container;
use Illuminate\Support\Facades\Facade;
use Illuminate\Database\Capsule\Manager as Capsule;

use Engine\WebSocket\Connection;

class Application implements ArrayAccess
{
    protected $connection = null;

    protected $db = null;

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
        $this->db = $database;
        // 设置允许静态使用
        Facade::setFacadeApplication([
            'db' =>$database->getDatabaseManager() ,
        ]);
    }

    // 初始化 Redis
    public function initRedis()
    {
        $config = config('database.redis');
        $redis = new Redis($config);
        Container::bind('redis' , $redis);
    }

    public function emptyRedis()
    {
        // 清空 redis
        redis()->flushAll();

    }

    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }

    public function run()
    {
        $this->initDatabase();
        $this->initRedis();
        $this->emptyRedis();
        $this->initWebSocket();
        $this->connection->run();
    }
}