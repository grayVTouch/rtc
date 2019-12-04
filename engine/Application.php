<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/9
 * Time: 17:29
 */

namespace Engine;

use App\Model\FriendModel;
use App\Model\ProjectModel;
use App\Model\UserModel;
use App\Model\UserOptionModel;
use Core\Lib\Hash;
use function core\obj_to_array;
use function core\random;
use Exception;

use Core\Lib\Redis;
use Engine\Facade\Redis as RedisFacade;
use Illuminate\Support\Facades\DB;
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
        $log->dir = config('app.log_dir');
        $log->prefix = 'runtime';
        FacadeLib::register('log' , $log);
    }

    /**
     * 系统初始化
     */
    public function initialize()
    {
        try {
            DB::beginTransaction();
            $system_waiter_name = config('app.system_waiter_name');
            // 为每个项目创建系统用户
            $project = ProjectModel::all();
            foreach ($project as $v)
            {
                $users = UserModel::getByIdentifier($v->identifier);
                foreach ($users as $v1)
                {
                    if (empty($v1->aes_key)) {
                        $aes_key = random(16 , 'mixed' , true);
                        UserModel::updateById($v1->id , [
                            'aes_key' => $aes_key
                        ]);
                    }
                }
                // 检查系统用户是否存在
                $system_user = UserModel::systemUser($v->identifier);
                if (!empty($system_user)) {
                    // 跑一遍用户列表，没有的统统成为好友关系
                    // 这是为了兼容测试阶段生成的用户
                    $users_for_user = UserModel::getByIdentifierAndRole($v->identifier , 'user');
                    foreach ($users_for_user as $v1)
                    {
                        $friend = FriendModel::findByUserIdAndFriendId($v1->id , $system_user->id);
                        if (empty($friend)) {
                            FriendModel::u_insertGetId($v1->id , $system_user->id);
                            FriendModel::u_insertGetId($system_user->id , $v1->id);
                        }
                    }
                    continue ;
                }
                $waiter_username = config('app.waiter_username');
                $waiter_password = config('app.waiter_password');
                $waiter_password = Hash::make($waiter_password);
                $area_code = config('app.waiter_area_code');
                $phone = config('app.waiter_phone');
                $full_phone = sprintf('%s%s' , $area_code , $phone);
                // 系统用户不存在，新增系统用户
                $id = UserModel::insertGetId([
                    'identifier' => $v->identifier ,
                    'area_code' => $area_code ,
                    'phone'    => $phone ,
                    'full_phone' => $full_phone ,
                    'username' => $waiter_username ,
                    'password' => $waiter_password ,
                    'is_system' => 1 ,
                    'is_temp' => 0 ,
                    'role' => 'admin' ,
                    'nickname' => $system_waiter_name ,
                    'is_init_password' => 1 ,
                ]);
                UserOptionModel::insertGetId([
                    'user_id' => $id
                ]);
            }
            DB::commit();
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
        $this->initialize();
        // 这个务必在最后执行！！
        // 因为 WebSocket 实例一旦创建成功
        // 那么实际上
        $this->initWebSocket();
    }
}