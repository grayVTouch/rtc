<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/10
 * Time: 17:22
 */

namespace Engine;


use App\WebSocket\Util\MessageUtil;
use Core\Lib\Facade;
use Core\Lib\Throwable;
use DateInterval;
use DateTime;
use DateTimeZone;
use App\Model\GroupModel;
use App\Model\GroupMemberModel;
use App\Model\GroupMessageModel;
use App\Model\GroupMessageReadStatusModel;
use App\Model\UserModel;
use App\Redis\MiscRedis;
use App\Redis\UserRedis;
use App\Util\PushUtil;
use Engine\Facade\Log;
use Illuminate\Support\Facades\DB;
use Exception;

use Swoole\Server;
use Swoole\Timer;
use Swoole\WebSocket\Server as BaseWebSocket;
use Swoole\Http\Request as Http;
use Swoole\Http\Response;


class WebSocket
{
    protected $config = [];

    protected $isOpen = false;

    public $websocket = null;

    public $http = null;

    protected $app = null;

    protected $identifier = null;

    protected $ip = '0.0.0.0';

    protected $port = 10000;

    protected $taskWorkerNum = 0;

    protected $workerNum = 1;

    protected $documentRoot = '';

    protected $enableReusePort = true;

    /**
     * 解析 WebSocket 配置参数
     */
    protected function parseConfig()
    {
        $this->ip               = config('app.ip');
        $this->port             = config('app.port');
        $this->taskWorkerNum    = config('app.task_worker');
        $this->workerNum        = config('app.worker');
        $this->documentRoot     = config('app.document_root');
        $this->enableReusePort  = config('app.reuse_port');
    }

    /**
     * @return array
     */
    protected function getConfig()
    {
        $option = [];
        if (!empty($this->taskWorkerNum)) {
            $option['task_worker_num'] = $this->taskWorkerNum;
        }
        if (!empty($this->workerNum)) {
            $option['worker_num'] = $this->workerNum;
        }
        if (!empty($this->enableReusePort)) {
            $option['enable_reuse_port'] = $this->enableReusePort;
        }
        if (!empty($this->documentRoot)) {
            $option['document_root'] = $this->documentRoot;
            $option['enable_static_handler'] = true;
        }
        return $option;
    }

    public function __construct(Application $app)
    {
        // 解析配置
        $this->parseConfig();
        $this->app = $app;
        $this->websocket = new BaseWebSocket($this->ip , $this->port);
        $this->websocket->set($this->getConfig());
        // 在 manager 进程中调用
        $this->websocket->on('WorkerStart' , [$this , 'workerStart']);
        // 在主进程中调用（Reactor 线程组实现高性能 tcp 监听）
        $this->websocket->on('open' , [$this , 'open']);
        $this->websocket->on('close' , [$this , 'close']);
        $this->websocket->on('task' , [$this , 'task']);
        $this->websocket->on('message' , [$this , 'message']);
        $this->websocket->on('request' , [$this , 'request']);
        // 开始运行程序
        $this->websocket->start();
    }

    public function workerStart(BaseWebSocket $websocket , int $worker_id)
    {
        Facade::register('websocket' , $this);
        $this->app->initDatabase();
        $this->app->initRedis();
        if ($worker_id != 0) {
            return ;
        }
        // 定时器仅需要开启一次！
        $this->initTimer();
    }

    /**
     * @param \Swoole\WebSocket\Server $websocket
     * @param \Swoole\Http\Request $http
     */
    public function open(BaseWebSocket $websocket , Http $http)
    {
        $this->isOpen = true;
        var_dump('存在客户端连接');
//        $websocket->push($http->fd , '你已经成功连接客户端');
    }

    public function close(Server $server , int $fd , int $reacter_id)
    {
        var_dump('存在客户端下线');
        $this->isOpen = false;
        $identifier = MiscRedis::fdMappingIdentifier($fd);
        if (empty($identifier)) {
            return ;
        }
        $user_id = UserRedis::fdMappingUserId($identifier , $fd);
        // 清除 Redis（删除的太快了）
        $user = UserModel::findById($user_id);
        if (!empty($user) && $user->role == 'admin') {
            try {
                DB::beginTransaction();
                $push = [];
                $conn = UserRedis::userIdMappingFd($identifier , $user->id);
                $conn = array_diff($conn , [$fd]);
                if (empty($conn)) {
                    // 如果是客服，用户加入的群组
                    $group = GroupMemberModel::getByUserId($user->id);
                    foreach ($group as $v)
                    {
                        $group_bind_waiter = UserRedis::groupBindWaiter($identifier , $v->group_id);
                        $group_bind_waiter = (int) $group_bind_waiter;
                        if ($group_bind_waiter != $user_id) {
                            // 绑定的并非当前离线客服
                            continue ;
                        }
                        $user_ids = GroupMemberModel::getUserIdByGroupId($v->group_id);
                        $group_message_id = GroupMessageModel::u_insertGetId($user->id , $v->group_id , 'text' , sprintf(config('business.message')['waiter_close'] , $user->username));
                        GroupMessageReadStatusModel::initByGroupMessageId($group_message_id , $v->group_id , $user->id);
                        $msg = GroupMessageModel::findById($group_message_id);
                        MessageUtil::handleGroupMessage($msg);
                        $push[] = [
                            'identifier'    => $v->group->identifier ,
                            'user_ids'      => $user_ids ,
                            'type'          => 'group_message' ,
                            'data'          => $msg
                        ];
                    }
                }
                DB::commit();
                foreach ($push as $v)
                {
                    PushUtil::multiple($v['identifier'] , $v['user_ids'] , $v['type'] , $v['data'] , [$fd]);
                }
            } catch(Exception $e) {
                DB::rollBack();
                $info = (new Throwable())->exceptionJsonHandlerInDev($e , true);
                $info = json_encode($info);
                if (!config('app.debug')) {
                    echo "onclose has exception: " . $info . PHP_EOL;
                    exit;
                }
                Log::log($info , 'exception');
            }
        }
        // 删除 Redis
        $this->clearRedis($user_id , $fd);
    }

    public function message(BaseWebSocket $server , $frame)
    {
        try {
            try {
                $data = json_decode($frame->data , true);
                if (!is_array($data)) {
                    $server->push($frame->fd , json_encode([
                        'code' => 400 ,
                        'data' => '数据格式不规范，请按照要求提供必要数据'
                    ]));
                    return ;
                }
            } catch(Exception $e) {
                $server->push($frame->fd , json_encode([
                    'code' => 400 ,
                    'data' => '数据解析异常，请按照要求提供必要数据'
                ]));
                return ;
            }
            // data 数据格式要求
            $default = [
                // 路由
                'router'    => 'user/test' ,
                // 调试模式
                'debug' => 'running' ,
                // 用户id
                'user_id' => 1 ,
                // 项目标识符
                'identifier' => 'abcd' ,
                // 除非 action = test，否则都需要携带 token
                'token'     => '123456789' ,
                // 登录的客户端
                'platform'  => 'pc' ,
                // 客户端生成的每次请求的标识符
                'request'  => '123' ,
                // 用户自定义传输的数据（也是要符合一定格式的数据）
                'data' => [
                    // 接收方（私聊）
                    'user_id' => 1 ,
                    // 接收方（群聊）
                    'group_id' => 1 ,
                    // 消息内容
                    'message' => '你好啊' ,
                    // 额外内容
                    'extra' => '' ,
                ] ,
            ];
            $data['router']     = $data['router'] ?? '';
            $data['identifier']      = $data['identifier'] ?? '';
            $data['debug']      = $data['debug'] ?? '';
            $data['user_id']      = $data['user_id'] ?? '';
            $data['token']      = $data['token'] ?? '';
            $data['platform']   = $data['platform'] ?? '';
            $data['request']   = $data['request'] ?? '';
            $data['data']       = $data['data'] ?? [];
//            print_r();
            $router = $this->parseRouter($data['router']);
            if (!$router) {
                $this->websocket->disconnect($frame->fd, 400, "未找到对应路由：{$data['router']}");
                return;
            }
            $namespace = 'App\WebSocket\\';
            $class = sprintf('%s%s' , $namespace , $router['class']);
            if (!class_exists($class)) {
                throw new Exception(" Class {$class} Not Found");
            }
            // 实例化对象
            $instance = new $class($this->websocket , $frame->fd , $data['identifier'] , $data['platform'] , $data['token'] , $data['request'] , $data['user_id'] , $data['debug']);
            // 执行前置操作
            if (method_exists($instance , 'before')) {
//                throw new Exception("Call to undefined method {$class}::before");
                $next = call_user_func([$instance , 'before']);
                if (!$next) {
                    return ;
                }
            }
            // 执行目标操作
            if (!method_exists($instance , $router['method'])) {
                throw new Exception("Call to undefined method {$class}::{$router['method']}");
            }

            call_user_func([$instance , $router['method']] , $data['data']);
            // 执行后置操作
            if (method_exists($instance , 'after')) {
//                throw new Exception("Call to undefined method {$class}::after");
                call_user_func([$instance , 'after']);
            }
            // 由于这个是长连接
            // 我怕他不会自动回收
            // 所以 手动销毁
            unset($instance);
        } catch(Exception $e) {
            $info = (new Throwable())->exceptionJsonHandlerInDev($e , true);
            if (config('app.debug')) {
                $server->push($frame->fd , json_encode([
                    'type' => 'error' ,
                    'data' => $info
                ]));
                return ;
            }
            Log::log(json_encode($info) , 'exception');
//            $server->disconnect($frame->fd , 500 , '服务器发生内部错误，服务器主动切断连接！请反馈错误信息给开发者');
            $server->push($frame->fd , json_encode([
                'code' => 500 ,
                'data' => '服务器发生内部错误，请反馈错误信息给开发者'
            ]));
        }
    }

    // 正向接口
    public function request(Http $request , Response $response)
    {
        try {
            // 支持跨域请求
            if ($request->server['request_method'] == 'OPTIONS') {
                $this->httpResponse($response , '' , 200);
                return ;
            }
            $router = $this->parseRouter($request->server['request_uri']);
            if (empty($router)) {
                $this->httpResponse($response , '请求的地址不正确' , 400);
                return ;
            }
            $param = $request->post;
            $param['identifier'] = $param['identifier'] ?? '';
            $namespace = 'App\Http\\';
            $class = sprintf('%s%s' , $namespace , $router['class']);
            if (!class_exists($class)) {
                throw new Exception(" Class {$class} Not Found");
            }
            // 实例化对象
            $instance = new $class($this->websocket , $request , $response , $param['identifier']);
            if (method_exists($instance , 'before')) {
//                throw new Exception("Call to undefined method {$class}::before");
                // 执行前置操作
                $next = call_user_func([$instance , 'before']);
                if (!$next) {
                    $this->httpResponse($response , '中间 before ');
                    return ;
                }
            }
            // 执行目标操作
            if (!method_exists($instance , $router['method'])) {
                throw new Exception("Call to undefined method {$class}::{$router['method']}");
            }
            call_user_func([$instance , $router['method']]);
            // 执行后置操作
            if (method_exists($instance , 'after')) {
//                throw new Exception("Call to undefined method {$class}::after");
                call_user_func([$instance , 'after']);
            }
            // 由于这个是长连接
            // 我怕他不会自动回收
            // 所以 手动销毁
            unset($instance);
        } catch(Exception $e) {
            $info = (new Throwable())->exceptionJsonHandlerInDev($e , true);
            if (config('app.debug')) {
                $this->httpResponse($response , (new Throwable())->exceptionJsonHandlerInDev($e , true) , 500);
                return ;
            }
            Log::log(json_encode($info) , 'exception');
        }
    }

    public function httpResponse(Response $response , $data = '' , $code = 200)
    {
        $response->header('Content-Type' , 'application/json');
        // 允许跨域
        $response->header('Access-Control-Allow-Origin' , '*');
        $response->header('Access-Control-Allow-Methods' , 'GET,POST,PUT,PATCH,DELETE');
        $response->header('Access-Control-Allow-Credentials' , 'false');
        $response->header('Access-Control-Allow-Headers' , 'Authorization,Content-Type,X-Request-With,Ajax-Request');
        $response->status(200);
        $response->end(json_encode([
            'code' => $code ,
            'data' => $data ,
        ]));
    }

    public function task(BaseWebSocket $server , $data)
    {

    }

    protected function initTimer()
    {
        // 单位：ms
        Timer::tick(30 * 1000 , function(){
            // 清理超过一定时间没有回复的咨询通道
            try {
                DB::beginTransaction();
                $group = GroupModel::serviceGroup();
                $max_duration = 4 * 60;
                $wait_duration = config('app.wait_duration');
                $push = [];
                foreach ($group as $v)
                {
                    $waiter_id = UserRedis::groupBindWaiter($v->identifier , $v->id);
                    if (empty($waiter_id)) {
                        continue ;
                    }
                    $waiter = UserModel::findById($waiter_id);
                    // 检查最近一条消息是否发送超时
                    $last_message = GroupMessageModel::recentMessage($waiter_id , $v->id , 'user');
//                print_r($last_message);
                    if (!empty($last_message)) {
                        $create_time = strtotime($last_message->create_time);
                        $duration = time() - $create_time;
                        if ($duration >= $max_duration || $duration <= $wait_duration) {
                            continue ;
                        }
                    }
                    UserRedis::delGroupBindWaiter($v->identifier , $v->id);
                    // 群通知：客服已经断开连接！
                    $user_ids = GroupMemberModel::getUserIdByGroupId($v->id);
                    $group_message_id = GroupMessageModel::u_insertGetId($waiter->id , $v->id , 'text' , sprintf(config('business.message')['waiter_leave'] , $waiter->username));
                    GroupMessageReadStatusModel::initByGroupMessageId($group_message_id , $v->id , $waiter->id);
                    $msg = GroupMessageModel::findById($group_message_id);
                    MessageUtil::handleGroupMessage($msg);
                    $push[] = [
                        'identifier' => $v->identifier ,
                        'user_ids'    => $user_ids ,
                        'type'       => 'group_message' ,
                        'data'       => $msg
                    ];
                }
                DB::commit();
                foreach ($push as $v)
                {
                    PushUtil::multiple($v['identifier'] , $v['user_ids'] , $v['type'] , $v['data']);
                }
            } catch(Exception $e) {
                DB::rollBack();
                $info = (new Throwable())->exceptionJsonHandlerInDev($e , true);
                $info = json_encode($info);
                if (config('app.debug')) {
                    echo '定时发生执行发生错误：' . $info;
                    exit;
                }
                Log::log($info , 'exception');
            }
        });

        // 60s 一次
        Timer::tick(60 * 1000 , function(){
//        Timer::tick(2 * 1000 , function(){
            $time = date('H:i:s' , time());
//            var_dump('60s 定时器一直在跑');
            if ($time == '03:30:00') {
                // 清理规则：清理前两天之前的数据
                // 清理那些临时创建的用户
                // 清理那些临时创建的组
                // 每天凌晨 3:30 执行一次
                try {
                    DB::beginTransaction();
                    $date_time = new DateTime();
                    // $date_time->setTimeZone(new DateTimeZone('Asia/Shanghai'));
                    $date_time->setTimestamp(strtotime(date('Y-m-d' , time())));
                    $date_time->sub(new DateInterval('P2D'));
                    $timestamp = $date_time->format('Y-m-d H:i:s');
                    $temp_user = UserModel::getTempByTimestamp($timestamp);
                    $clear_group = function($group_id){
                        // 清理临时群
                        GroupModel::destroy($group_id);
                        // 清理临时群中的成员
                        GroupMemberModel::delByGroupId($group_id);
                        // 群聊消息
                        GroupMessageModel::delByGroupId($group_id);
                        // 清理群聊已读/未读
                        GroupMessageReadStatusModel::delByGroupId($group_id);
                    };
                    if (!empty($temp_user)) {
                        /**
                         * **************
                         * 清理临时用户
                         * **************
                         */
                        foreach ($temp_user as $v)
                        {
                            UserModel::destroy($v->id);
                            GroupMemberModel::delByUserId($v->id);
                            $group_ids = GroupMemberModel::getGroupIdByUserId($v->id);
                            foreach ($group_ids as $v1)
                            {
                                $clear_group($v1);
                            }
                            // 清理 Redis key
                            $this->clearRedis($v->id);
                        }
                    }
                    /**
                     * **************
                     * 清理临时群
                     * **************
                     */
                    $temp_group = GroupModel::getTempByTimestamp($timestamp);
                    if (!empty($temp_group)) {
                        foreach ($temp_group as $v)
                        {
                            $clear_group($v->id);
                        }
                    }
                    DB::commit();
                } catch(Exception $e) {
                    DB::rollBack();
                }
            }
        });

        // todo ping 检测客户端是否仍然在线
    }

    // 清理 Redis
    public function clearRedis($user_id , $fd = null)
    {
        $user = UserModel::findById($user_id);
        if (empty($user)) {
            return ;
        }
        UserRedis::delNumberOfReceptionsForWaiter($user->identifier , $user->id);
        if (empty($fd)) {
            $fds = $fds = UserRedis::userIdMappingFd($user->identifier , $user->id);
        } else {
            $fds = [$fd];
        }
        foreach ($fds as $v)
        {
            UserRedis::delFdByUserId($user->identifier , $user_id , $v);
            UserRedis::delFdMappingUserId($user->identifier , $v);
            MiscRedis::delfdMappingIdentifier($v);
        }
        if (!UserRedis::isOnline($user->identifier , $user->id)) {
            // 确定当前已经处于完全离线状态，那么删除掉该用户绑定的相关信息
            $group_ids = GroupMemberModel::getGroupIdByUserId($user->id);
            array_walk($group_ids , function($v) use($user){
                UserRedis::delGroupBindWaiter($user->identifier , $v);
                UserRedis::delNoWaiterForGroup($user->identifier , $v);
            });
        }
    }

    // 解析客户端路由
    protected function parseRouter(string $router = '')
    {
        if (empty($router)) {
            $router = 'Index/index';
        }
        $router = ltrim($router , '/');
        $res = explode('/' , $router);
        if (count($res) != 2) {
            return false;
        }
        return [
            'class'     => $res[0] ,
            'method'    => $res[1] ,
        ];
    }

    // 发送数据
    public function push(int $fd , string $data = '')
    {
        $this->websocket->push($fd , $data);
    }

    // 是否存在
    public function exist(int $fd)
    {
        return $this->websocket->exist($fd);
    }

    public function run()
    {
        return $this->websocket->start();
    }
}