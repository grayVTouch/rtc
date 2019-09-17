<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/9
 * Time: 17:29
 */

namespace Engine;

use Core\Lib\Log;
use Exception;

use Core\Lib\Redis;
use Engine\Facade\Redis as RedisFacade;
use Illuminate\Support\Facades\Facade;
use Illuminate\Database\Capsule\Manager as Capsule;
use Core\Lib\Facade as FacadeLib;

class Application
{
    /**
     * @see \Engine\WebSocket
     */
    protected $websocket;

    /*
     * @see Illuminate\Database\Capsule\Manager
     */
    protected $db;

    /**
     * @see \Engine\WebSocket
     */
    protected $http;


    public function __construct()
    {

    }

    /**
     * 初始化 websocket
     */
    private function initWebSocket()
    {
        $this->websocket = new WebSocket($this);
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
        $this->db = $database;
        // 使其支持门面的调用方式
        // 必须使用 Laravel 的门面
        // 因为 DB::class 门面类继承的使
        // Laravel 的 Facade
        Facade::setFacadeApplication([
            'db' =>$database->getDatabaseManager() ,
        ]);
    }

    // 初始化 Redis
    public function initRedis()
    {
        $config = config('database.redis');
        $redis = new Redis($config);
        // todo 不采用容器的方式
//        Container::bind('redis' , $redis);
        // todo 采用门面的方式
        FacadeLib::register('redis' , $redis);
    }

    public function clearRedis()
    {
        // 清空 redis
        RedisFacade::flushAll();
    }

    /**
     * 初始化 http 服务器
     *
     * @throws Exception
     */
    public function initHttp()
    {
        $pid = pcntl_fork();
        if ($pid < 0) {
            throw new Exception("创建子进程失败");
        }
        if ($pid > 0) {
            return ;
        }
        $this->http = new Http();
        // 子进程退出
        exit;
    }

    /**
     * *****************
     * 初始化日志操作
     * *****************
     */
    public function initLog()
    {
        $log = new Log();
        $log->dir = __DIR__ . '/../';
        $log->prefix = 'runtime';
        FacadeLib::register('log' , $log);
    }

    /**
     * 开始运行程序
     *
     * @throws Exception
     */
    public function run()
    {
        $this->initDatabase();
        $this->initRedis();
        $this->clearRedis();
        $this->initLog();
//        $this->initHttp();
        // 这个务必在最后执行！！
        // 因为 WebSocket 实例一旦创建成功
        // 那么实际上
        $this->initWebSocket();
    }
}