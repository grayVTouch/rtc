<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/10
 * Time: 17:22
 */

namespace Engine;


use App\Model\ClearTimerLogModel;
use App\Model\DeleteMessageForGroupModel;
use App\Model\DeleteMessageForPrivateModel;
use App\Model\DeleteMessageModel;
use App\Model\FriendModel;
use App\Model\MessageModel;
use App\Model\MessageReadStatusModel;
use App\Model\ProgramErrorLogModel;
use App\Model\TaskLogModel;
use App\Model\TimerLogModel;
use App\Model\SessionModel;
use App\Redis\SessionRedis;
use App\Redis\TimerRedis;
use App\Util\ChatUtil;
use App\Util\GroupMessageUtil;
use App\Util\GroupUtil;
use App\Util\TimerLogUtil;
use App\Util\UserUtil;
use App\WebSocket\Util\MessageUtil;
use Core\Lib\Facade;
use Core\Lib\Throwable;
use function core\obj_to_array;
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
use App\Util\MessageUtil as BaseMessageUtil;
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
     * @throws \Exception
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

    /**
     * WebSocket constructor.
     *
     * @param \Engine\Application $app
     * @throws \Exception
     */
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
        $this->websocket->on('finish' , [$this , 'taskFinish']);

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
        var_dump('env: ' . ENV . '; ' .  date('Y-m-d H:i:s') . ' 存在客户端连接');
//        $websocket->push($http->fd , '你已经成功连接客户端');
    }


    public function close(Server $server , int $fd , int $reacter_id)
    {
        $this->isOpen = false;
        $identifier = MiscRedis::fdMappingIdentifier($fd);
        if (empty($identifier)) {
            return ;
        }
        $user_id = UserRedis::fdMappingUserId($identifier , $fd);
        $conn = UserRedis::userIdMappingFd($identifier , $user_id);
        $_conn = is_array($conn) ? array_diff($conn , [$fd]) : [];

        if (empty($_conn)) {
            // 所有客户端均已经下线
            if (!empty($user_id)) {
                // 记录当前用户最近一次下线时间
                UserRedis::userRecentOnlineTimestamp($identifier , $user_id , date('Y-m-d H:i:s'));
            }
            UserUtil::onlineStatusChange($identifier , $user_id , 'offline');
            var_dump('env: ' . ENV . '; identifier: ' . $identifier . '; ' . date('Y-m-d H:i:s') . '; user_id: ' . $user_id . ' 对应的某客户端下线（还有其他客户端活跃）');
        } else {
            var_dump('env: ' . ENV . '; identifier: ' . $identifier . '; ' . date('Y-m-d H:i:s') . '; user_id: ' . $user_id . ' 客户端下线（所有对应客户端下线）');
        }
        // 清除 Redis（删除的太快了）
        $user = UserModel::findById($user_id);
        try {
            DB::beginTransaction();
            $push = [];
            if (!empty($user)) {
                if (empty($_conn)) {
                    // 客服下线后续处理
//                    if ($user->role == 'admin') {
//                        // 如果是客服，自动退出客服群
//                        $groups = GroupMemberModel::getByUserId($user->id);
//                        foreach ($groups as $v)
//                        {
//                            $group_bind_waiter = UserRedis::groupBindWaiter($identifier , $v->group_id);
//                            $group_bind_waiter = (int) $group_bind_waiter;
//                            if ($group_bind_waiter != $user_id) {
//                                // 绑定的并非当前离线客服
//                                continue ;
//                            }
//                            $user_ids = GroupMemberModel::getUserIdByGroupId($v->group_id);
//                            $group_message_id = GroupMessageModel::u_insertGetId($user->id , $v->group_id , 'text' , sprintf(config('business.message')['waiter_close'] , $user->username));
//                            GroupMessageReadStatusModel::initByGroupMessageId($group_message_id , $v->group_id , $user->id);
//                            $msg = GroupMessageModel::findById($group_message_id);
//                            MessageUtil::handleGroupMessage($msg);
//                            $push[] = [
//                                'identifier'    => $v->user->identifier ,
//                                'user_ids'      => $user_ids ,
//                                'type'          => 'group_message' ,
//                                'data'          => $msg
//                            ];
//                        }
//                    }
                    // 用户离线后自动退出会话
                    $sessions = SessionModel::getByUserId($user->id);
                    foreach ($sessions as $v)
                    {
                        SessionRedis::delSessionMember($user->identifier , $v->session_id , $user->id);
                    }
                }
            }
            DB::commit();
            foreach ($push as $v)
            {
                PushUtil::multiple($v['identifier'] , $v['user_ids'] , $v['type'] , $v['data'] , [$fd]);
            }
            // 删除 Redis
            $this->clearRedis($user_id , $fd);
        } catch(Exception $e) {
            DB::rollBack();
            if (config('app.debug')) {
                throw $e;
            }
            $log = (new Throwable())->exceptionJsonHandlerInDev($e , true);
            $log = json_encode($log);
            ProgramErrorLogModel::u_insertGetId('WebSocket 请求执行异常' , $log , 'WebSocket');
        }
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
            $log = (new Throwable())->exceptionJsonHandlerInDev($e , true);
            $log = json_encode($log);
            if (config('app.debug')) {
                $server->push($frame->fd , json_encode([
                    'type' => 'error' ,
                    'data' => $log
                ]));
                throw $e;
            }
            ProgramErrorLogModel::u_insertGetId('WebSocket 请求执行异常' , $log , 'WebSocket');
            $server->push($frame->fd , json_encode([
                'code' => 500 ,
                'data' => '服务器发生内部错误，请联系开发人员'
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
            $router = $this->parseHttpRouter($request->server['request_uri']);
            if (empty($router)) {
                $this->httpResponse($response , '请求的地址不正确' , 400);
                return ;
            }
            $param['identifier'] = $request->post['identifier'] ?? ($request->get['identifier'] ?? '');
            $namespace = 'App\Http';
            $class = sprintf('%s\%s\Controller\%s' , $namespace , $router['module'] , $router['class']);
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
                    // todo 如果程序中没有针对中间件中断操作的返回，那么需要放开这边的注释，否则问题排查比较困难
//                    $this->httpResponse($response , '中间 before ');
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
            $log = (new Throwable())->exceptionJsonHandlerInDev($e , true);
            $log = json_encode($log);
            if (config('app.debug')) {
                $this->httpResponse($response , $log , 500);
                throw $e;
            }
            ProgramErrorLogModel::u_insertGetId('Http 请求执行异常' , $log , 'Http');
            $this->httpResponse($response , json_encode([
                'code' => 500 ,
                'data' => '服务器发生异常，请联系开发人员'
            ]) , 500);
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

    public function task(BaseWebSocket $server , $task_id , $from_id , $data)
    {
        try {
            $data = json_decode($data , true);
            if (empty($data)) {
                // 如果没有任何数据
                TaskLogModel::u_insertGetId('异步任务执行失败，原因：原始数据为空');
                return ;
            }
            switch ($data['type'])
            {
                case 'callback':
                    // app 极光推送
                    $callback   = $data['data']['callback'];
                    $param      = $data['data']['param'];
                    $res = call_user_func_array($callback , $param);
                    TaskLogModel::u_insertGetId(json_encode($res) , json_encode($data) , '任务成功运行');
                    break;
            }
        } catch(Exception $e) {
            $log = (new Throwable())->exceptionJsonHandlerInDev($e , true);
            $log = json_encode($log);
            if (config('app.debug')) {
                throw $e;
            }
            ProgramErrorLogModel::u_insertGetId('WebSocket 任务执行异常' , $log , 'Task');
        }
    }

    public function taskFinish(BaseWebSocket $server , $data)
    {
        // 异步任务结束
    }

    /**
     * 定时任务
     */
    protected function initTimer()
    {
        /**
         * 客服自动退出
         */
//        Timer::tick(2 * 1000 , function(){
//            $timer_log_id = 0;
//            TimerLogUtil::logCheck(function() use(&$timer_log_id){
//                $timer_log_id = TimerLogModel::u_insertGetId('客服通话检测中...' , 'platform_advoise');
//            });
//            try {
//                DB::beginTransaction();
//                $groups = GroupModel::serviceGroup();
//                $waiter_wait_max_duration = config('app.waiter_wait_max_duration');
//                $push = [];
//                foreach ($groups as $v)
//                {
//                    if (empty($user)) {
//                        // 如果没有找到用户信息，跳过不处理
//                        continue ;
//                    }
//                    $waiter_id = UserRedis::groupBindWaiter($v->user->identifier , $v->id);
//                    if (empty($waiter_id)) {
//                        // 没有绑定任何客服
//                        continue ;
//                    }
//                    $waiter = UserModel::findById($waiter_id);
//                    // 检查最近一条消息是否发送超时
//                    $last_message = GroupMessageModel::recentMessage($waiter_id , $v->id , 'user');
//                    if (!empty($last_message)) {
//                        $create_time = strtotime($last_message->create_time);
//                        $free_duration = time() - $create_time;
//                        if ($free_duration < $waiter_wait_max_duration) {
//                            // 等待时间没有超过最长客服等待时间，跳过
//                            continue ;
//                        }
//                    }
//                    UserRedis::delGroupBindWaiter($v->user->identifier , $v->id);
//                    // 群通知：客服已经断开连接！
//                    $user_ids = GroupMemberModel::getUserIdByGroupId($v->id);
//                    $group_message_id = GroupMessageModel::u_insertGetId($waiter->id , $v->id , 'text' , sprintf(config('business.message')['waiter_leave'] , $waiter->username));
//                    GroupMessageReadStatusModel::initByGroupMessageId($group_message_id , $v->id , $waiter->id);
//                    $msg = GroupMessageModel::findById($group_message_id);
//                    MessageUtil::handleGroupMessage($msg);
//                    $push[] = [
//                        'identifier' => $v->user->identifier ,
//                        'user_ids'    => $user_ids ,
//                        'type'       => 'group_message' ,
//                        'data'       => $msg
//                    ];
//                }
//                DB::commit();
//                foreach ($push as $v)
//                {
//                    PushUtil::multiple($v['identifier'] , $v['user_ids'] , $v['type'] , $v['data']);
//                }
//                TimerLogUtil::logCheck(function() use($timer_log_id){
//                    TimerLogModel::appendById($timer_log_id , '执行成功，结束');
//                });
//            } catch(Exception $e) {
//                DB::rollBack();
//                TimerLogUtil::logCheck(function() use($timer_log_id){
//                    TimerLogModel::appendById($timer_log_id , '执行发生异常，结束');
//                });
//                if (config('app.debug')) {
//                    throw $e;
//                }
//                $log = (new Throwable())->exceptionJsonHandlerInDev($e , true);
//                $log = json_encode($log);
//                ProgramErrorLogModel::u_insertGetId('客服自动退出定时器执行发生异常' , $log , 'timer_event');
//            }
//        });

        /**
         * todo 清理临时群 + 临时用户（数据量大时必须更改！）
         */
        Timer::tick( 1 * 3600 * 1000 , function(){
            // 记录定时执行日志
            $timer_log_id = 0;
            TimerLogUtil::logCheck(function() use(&$timer_log_id){
                $timer_log_id = TimerLogModel::u_insertGetId('清理临时群 + 用户中...' , 'clear_tmp_group_and_user');
            });
            $date = date('Y-m-d');
            $once_for_clear_tmp_group_timer = TimerRedis::onceForClearTmpGroupTimer();
            if (!empty($once_for_clear_tmp_group_timer) && $once_for_clear_tmp_group_timer == $date) {
                // 今天已经执行过了
                TimerLogUtil::logCheck(function() use($timer_log_id){
                    TimerLogModel::appendById($timer_log_id , '今天已经执行过了，结束');
                });
                return ;
            }
            $time = date('H:i:s' , time());
            $time_point_for_clear_tmp_group_timer = config('app.time_point_for_clear_tmp_group_and_user_timer');
            if ($time < $time_point_for_clear_tmp_group_timer) {
                TimerLogUtil::logCheck(function() use($timer_log_id){
                    TimerLogModel::appendById($timer_log_id , '还未到执行的时间点，结束');
                });
                return ;
            }
            // 清理规则：清理前两天之前的数据
            // 清理那些临时创建的用户
            // 清理那些临时创建的组
            // 每天凌晨 3:30 执行一次
            try {
                DB::beginTransaction();
                $date_time = new DateTime();
                // $date_time->setTimeZone(new DateTimeZone('Asia/Shanghai'));
                $date_time->setTimestamp(time());
                $date_interval = new DateInterval('P2D');
//                $date_interval = new DateInterval('PT10S');
                $date_time->sub($date_interval);
                $timestamp = $date_time->format('Y-m-d H:i:s');
                $temp_user = UserModel::getTempByTimestamp($timestamp);
                if (!empty($temp_user)) {
                    /**
                     * **************
                     * 清理临时用户
                     * **************
                     */
                    foreach ($temp_user as $v)
                    {
                        UserUtil::delete($v->identifier , $v->id);
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
                        GroupUtil::delete($v->identifier , $v->id);
                    }
                }
                DB::commit();
                TimerRedis::onceForClearTmpGroupTimer($date);
                TimerLogUtil::logCheck(function() use($timer_log_id){
                    TimerLogModel::appendById($timer_log_id , '执行成功，结束');
                });
            } catch(Exception $e) {
                DB::rollBack();
                TimerLogUtil::logCheck(function() use($timer_log_id){
                    TimerLogModel::appendById($timer_log_id , '执行异常，结束');
                });
                if (config('app.debug')) {
                    throw $e;
                }
                // 记录错误日志
                $log = (new Throwable())->exceptionJsonHandlerInDev($e , true);
                $log = json_encode($log);
                ProgramErrorLogModel::u_insertGetId('清理临时群的定时器执行发生错误' , $log , 'timer_event');
            }
        });

        /**
         * todo 清理到期的时效群（数据量过大时这种方式不行！后期必须更换）
         */
        Timer::tick(30 * 1000 , function(){
//        Timer::tick(2 * 1000 , function(){
//        Timer::tick(10 * 1000 , function(){
            // 时效群
            $timer_log_id = 0;
            TimerLogUtil::logCheck(function() use(&$timer_log_id){
                $timer_log_id = TimerLogModel::u_insertGetId('清理过期的时效群中...' , 'clear_expired_group');
            });
            try {
                DB::beginTransaction();
                $expired_group = GroupModel::expiredGroup();
                foreach ($expired_group as $v)
                {
                    $v->member_ids = GroupMemberModel::getUserIdByGroupId($v->id);
                    // 删除群
                    GroupUtil::delete($v->identifier , $v->id);
                }
                DB::commit();
                foreach ($expired_group as $v)
                {
                    if (empty($v->user)) {
                        continue ;
                    }
                    // 通知群成员删除群相关信息
                    PushUtil::multiple($v->user->identifier , $v->member_ids , 'delete_group_from_cache' , [$v->id]);
                }
                TimerLogUtil::logCheck(function() use($timer_log_id){
                    TimerLogModel::appendById($timer_log_id , '执行成功，结束');
                });
            } catch(Exception $e) {
                DB::rollBack();
                TimerLogUtil::logCheck(function() use($timer_log_id){
                    TimerLogModel::appendById($timer_log_id , '执行异常，结束');
                });
                if (config('app.debug')) {
                    throw $e;
                }
                // 记录错误日志
                $log = (new Throwable())->exceptionJsonHandlerInDev($e , true);
                $log = json_encode($log);
                ProgramErrorLogModel::u_insertGetId('清理时效群的定时器执行发生错误' , $log , 'timer_event');
            }
        });

        /**
         * todo 清理消息记录（数据量大时不行！后期必须更改）
         */
        Timer::tick(12 * 3600 * 1000 , function(){
//        Timer::tick(2 * 1000 , function(){
            $timer_log_id = 0;
            TimerLogUtil::logCheck(function() use(&$timer_log_id){
                $timer_log_id = TimerLogModel::u_insertGetId('消息记录清理中...' , 'clear_message');
            });
            /**
             * 该定时器主要作用是清理聊天记录
             */
            $time = date('H:i:s');
            $timestamp_for_now = time();
            // 定时清理聊天记录
            $time_point_for_clear_private_message_timer = config('app.time_point_for_clear_private_message_timer');
            $time_point_for_clear_group_message_timer   = config('app.time_point_for_clear_group_message_timer');

            $get_duration = function(string $type){
                switch ($type)
                {
                    case 'day':
                        $duration = 24 * 3600;
                        break;
                    case 'week':
                        $duration = 7 * 24 * 3600;
                        break;
                    case 'month':
                        $duration = 30 * 24 * 3600;
                        break;
                    default:
                        $duration = 0;
                }
                return $duration;
            };

            // 获取已经开启定时清理的用户记录
            $user_for_clear_private = [];
            $user_for_clear_group = [];
            try {
                DB::beginTransaction();
                if ($time >= $time_point_for_clear_private_message_timer) {
                    // 清理私聊记录
                    $user_for_clear_private = UserModel::getWithEnableRegularClearForPrivate();
                    foreach ($user_for_clear_private as $v)
                    {
                        $last = ClearTimerLogModel::lastByTypeAndUserId('private' , $v->id);
                        if (empty($last)) {
                            ClearTimerLogModel::u_insertGetId($v->id , 'private');
                            continue ;
                        }
                        $timestamp_for_last = strtotime($last->create_time);
                        $duration = $get_duration($v->user_option->clear_timer_for_private);
                        if ($timestamp_for_now - $timestamp_for_last < $duration) {
                            // 未超过时间
                            continue ;
                        }
                        // 清空所有私聊记录
                        $friend_ids = FriendModel::getFriendIdByUserId($v->id);
                        $v->friend_ids = $friend_ids;
                        $v->chat_ids = [];
                        foreach ($friend_ids as $v1)
                        {
                            $chat_id = ChatUtil::chatId($v->id , $v1);
                            $user_for_clear_private->chat_ids[] = $chat_id;
                            $messages = MessageModel::getByChatId($chat_id);
                            foreach ($messages as $v2)
                            {
                                $reference_count = DeleteMessageForPrivateModel::countByChatIdAndMessageId($chat_id , $v2->id);
                                $reference_count++;
                                if ($reference_count >= 2) {
                                    // 删除记录
                                    BaseMessageUtil::delete($v2->id);
                                } else {
                                    // 屏蔽消息记录
                                    DeleteMessageForPrivateModel::u_insertGetId($v2->identifier , $v->id , $v2->id , $chat_id);
                                }
                            }
                        }
                        ClearTimerLogModel::u_insertGetId($v->id , 'private');
                    }
                    // todo 通知客户端删除本地数据库中的数据
                }

                if ($time == $time_point_for_clear_group_message_timer) {
                    // 清理群聊记录
                    $user_for_clear_group = UserModel::getWithEnableRegularClearForGroup();
                    foreach ($user_for_clear_group as $v)
                    {
                        $last = ClearTimerLogModel::lastByTypeAndUserId('group' , $v->id);
                        if (empty($last)) {
                            ClearTimerLogModel::u_insertGetId($v->id , 'group');
                            continue ;
                        }
                        $timestamp_for_last = strtotime($last->create_time);
                        $duration = $get_duration($v->user_option->clear_timer_for_group);
                        if ($timestamp_for_now - $timestamp_for_last < $duration) {
                            // 未超过时间
                            continue ;
                        }
                        // 清空所有私聊记录
                        $groups = GroupMemberModel::getByUserId($v->id);
                        $v->groups = $groups;
                        $v->group_ids = [];
                        foreach ($groups as $v1)
                        {
                            $v->group_ids[] = $v1->id;
                            $group_messages = GroupMessageModel::getByGroupId($v1->group_id);
                            $member_count = GroupMemberModel::countByGroupId($v1->group_id);
                            foreach ($group_messages as $v2)
                            {
                                $reference_count = DeleteMessageForGroupModel::countByGroupIddAndGroupMessageId($v1->group_id , $v2->id);
                                $reference_count++;
                                if ($reference_count >= $member_count) {
                                    // 删除记录
                                    GroupMessageUtil::delete($v2->id);
                                } else {
                                    // 屏蔽消息记录
                                    DeleteMessageForGroupModel::u_insertGetId($v2->identifier , $v->id , $v2->id , $v1->group_id);
                                }
                            }
                        }
                        ClearTimerLogModel::u_insertGetId($v->id , 'group');
                    }
                }
                DB::commit();
                foreach ($user_for_clear_private as $v)
                {
                    // 通知客户端清除本地缓存
                    PushUtil::multiple($v->identifier , $v->id , 'empty_private_session_from_cache' , $v->chat_ids);
                }
                foreach ($user_for_clear_group as $v)
                {
                    // 通知客户端清除本地缓存
                    PushUtil::multiple($v->identifier , $v->id , 'empty_group_session_from_cache' , $v->group_ids);
                }
                TimerLogUtil::logCheck(function() use(&$timer_log_id){
                    TimerLogModel::appendById($timer_log_id , '执行成功，结束');
                });
            } catch(Exception $e) {
                DB::rollBack();
                TimerLogUtil::logCheck(function() use($timer_log_id){
                    TimerLogModel::appendById($timer_log_id , '执行异常，结束');
                });
                if (config('app.debug')) {
                    throw $e;
                }
                // 记录错误日志
                $log = (new Throwable())->exceptionJsonHandlerInDev($e , true);
                $log = json_encode($log);
                ProgramErrorLogModel::u_insertGetId('清理消息记录的定时器执行发生错误' , $log , 'timer_event');
            }
        });

    }

    /**
     * 清理 Redis
     * 如果未提供第二个参数，则是清理某个用户所有相关的 redis
     * 如果提供了第二个参数，则是清理指定的客户端连接的 redis
     *
     * @param int $user_id
     * @param int|null $fd
     */
    public function clearRedis(int $user_id , int $fd = null)
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
            UserRedis::delFdMappingPlatform($user->identifier , $v);
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

    // 解析客户端路由
    protected function parseHttpRouter(string $router = '')
    {
        if (empty($router)) {
            $router = 'Index/Index/index';
        }
        $router = ltrim($router , '/');
        $res = explode('/' , $router);
        if (count($res) != 3) {
            return false;
        }
        return [
            'module'    => $res[0] ,
            'class'     => $res[1] ,
            'method'    => $res[2] ,
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

    // 投递异步任务
    public function deliveryTask(string $data = null)
    {
        return $this->websocket->task($data);
    }

    public function run()
    {
        return $this->websocket->start();
    }
}