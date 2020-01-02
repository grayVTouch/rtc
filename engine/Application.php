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
use App\Redis\QueueRedis;
use function core\array_unit;
use Core\Lib\File;
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
     * ********************
     * 系统初始化
     * ********************
     */
    public function initialize()
    {
//        // 初始化系统标志
//        $initialized = config('app.initialized');
//        if (File::isFile($initialized)) {
//            // 已经初始化过系统，跳过
//            return ;
//        }
        try {
            DB::beginTransaction();
            // 系统客服
            $system_user_data = [
                'username' => config('app.waiter_username') ,
                'password' => config('app.waiter_password') ,
                'area_code' => config('app.waiter_area_code') ,
                'phone' => config('app.waiter_phone') ,
                'nickname' => config('app.system_waiter_name') ,
                'avatar' => config('app.system_waiter_avatar') ,
                // 系统用户
                'is_system' => 1 ,
                // 测试用户
                'is_test' => 0 ,
                // 是否初始过密码
                'is_init_password' => 1 ,
                // 是否初始化密码
                'is_temp' => 0 ,
                // 角色
                'role' => 'admin' ,
            ];
            $system_user_data['password'] = Hash::make($system_user_data['password']);
            $system_user_data['full_phone'] = sprintf('%s%s' , $system_user_data['area_code'] , $system_user_data['phone']);
            // 为每个项目创建系统用户
            $project = ProjectModel::all();
            foreach ($project as $v)
            {
                /**
                 * 产生系统客服
                 * 并且和每个现有用户自动成为好友关系
                 */
                $system_user = UserModel::systemUser($v->identifier);
                if (empty($system_user)) {
                    // 如果系统客服不存在，那么创建一个系统客服
                    // 系统用户不存在，新增系统用户
                    $copy_system_user_data = $system_user_data;
                    $copy_system_user_data['identifier'] = $v->identifier;
                    $id = UserModel::insertGetId(array_unit($copy_system_user_data , [
                        'identifier' ,
                        'username' ,
                        'nickname' ,
                        'avatar' ,
                        'password' ,
                        'area_code' ,
                        'phone' ,
                        'full_phone' ,
                        'is_system' ,
                        'is_temp' ,
                        'is_test' ,
                        'role' ,
                        'is_init_password' ,
                    ]));
                    UserOptionModel::insertGetId([
                        'user_id' => $id
                    ]);
                    $system_user = UserModel::findById($id);
                }

                /**
                 * aes 加密解密相关数据补全
                 */
                $users = UserModel::getByIdentifier($v->identifier);

                /**
                 * 初始化相关操作
                 */
                foreach ($users as $v1)
                {
                    if (empty($v1->aes_key)) {
                        // 初始化aes加密解密
                        $aes_key = random(16 , 'mixed' , true);
                        UserModel::updateById($v1->id , [
                            'aes_key' => $aes_key
                        ]);
                    }
                    if ($v1->role == 'user') {
                        // 初始化普通用户的客服关系
                        $friend = FriendModel::findByUserIdAndFriendId($v1->id , $system_user->id);
                        if (empty($friend)) {
                            FriendModel::u_insertGetId($v->identifier , $v1->id , $system_user->id);
                            FriendModel::u_insertGetId($v->identifier , $system_user->id , $v1->id);
                        }
                    }
                }

                /**
                 * 相关热数据缓存
                 * 私聊消息已读|未读
                 * 群聊消息已读|未读
                 * 私聊消息数量
                 * 群聊消息数量
                 *
                 * 后续消息更新都是对 redis|mysql 的同步更新
                 * 关键就是 启动 swoole 的时候缓存这些常用数据
                 * 关闭 swoole 的时候销毁这些数据
                 * 运行期间，如果数据发生变更
                 * 那么 redis|mysql 上的数据需要同步更新
                 */

            }
            DB::commit();
            // 创建安装标志
//            File::cFile($initialized);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 预加载相关数据有
     */
    public function dataPreload()
    {

    }

    /**
     * app 异步推送队列
     */
    public function consumeQueue()
    {
        $consume_queue_process = config('app.consume_queue_process');
        $pids = [];
        for ($i = 1; $i <= $consume_queue_process; ++$i)
        {
            $pid = pcntl_fork();
            if ($pid < 0) {
                throw new Exception("创建子进程失败");
            }
            if ($pid == 0) {
                // 在每个子进程中创建 redis 连接
                // 必须，不允许单个 redis 连接被多个进程共享
                $this->initDatabase();
                $this->initRedis();
                // 子进程
                while (true)
                {
                    // 消费队列
                    $res = QueueRedis::shift();
//                    var_dump("队列消费执行中 ... ");
                    if (empty($res)) {
                        // 队列已经被消费完毕
                        // 等待 1 s 后在处理
                        sleep(1);
                        continue ;
                    }
                    $res = json_decode($res , true);
                    /**
                     * 数据结构如下
                     *
                     * [
                     *      'callback' => [] ,
                     *      'param' => [] ,
                     * ]
                     */
                    if (empty($res)) {
                        // 数据格式不规范
                        continue;
                    }
                    // 执行队列中缓存的事件
                    call_user_func_array($res['callback'] , $res['param']);
                }
                // 子进程执行完毕后必须要退出
                exit;
            }
            // 父进程
            $pids[] = $pid;
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
        $this->dataPreload();
        $this->consumeQueue();
        // 这个务必在最后执行！！
        // 因为 WebSocket 实例一旦创建成功
        // 那么实际上
        $this->initWebSocket();
    }
}