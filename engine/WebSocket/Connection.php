<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/10
 * Time: 17:22
 */

namespace Engine\WebSocket;


use App\Util\Misc;
use Core\Lib\Throwable;
use DateInterval;
use DateTime;
use DateTimeZone;
use App\Model\Group;
use App\Model\GroupMember;
use App\Model\GroupMessage;
use App\Model\GroupMessageReadStatus;
use App\Model\User;
use App\Redis\MiscRedis;
use App\Redis\UserRedis;
use App\Util\Push;
use Illuminate\Support\Facades\DB;


use Core\Lib\Container;

use Engine\Application;
use Exception;

use Swoole\Server;
use Swoole\Timer;
use Swoole\WebSocket\Server as WebSocket;
use Swoole\Http\Request as Http;
use Swoole\Http\Response;



class Connection
{
    protected $config = [];

    protected $isOpen = false;

    public $websocket = null;

    public $http = null;

    protected $app = null;

    protected $identifier = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->config       = config('app.websocket');
        $this->initialize();
    }

    protected function initialize()
    {
        $this->websocket = new Websocket($this->config['ip'] , $this->config['port']);
        // 设置进程数量
        $this->websocket->set([
            'task_worker_num'   => $this->config['task_worker'] ,
            'worker_num'        => $this->config['worker'] ,
            'enable_reuse_port' => $this->config['reuse_port'] ,
        ]);
        $this->websocket->on('WorkerStart' , [$this , 'workerStart']);
        // 子进程内部调用
        $this->websocket->on('open' , [$this , 'open']);
        $this->websocket->on('close' , [$this , 'close']);
        $this->websocket->on('task' , [$this , 'task']);
        $this->websocket->on('message' , [$this , 'message']);
        $this->websocket->on('request' , [$this , 'request']);
        Container::bind('websocket' , $this->websocket);
    }

    public function workerStart(WebSocket $websocket , int $worker_id)
    {
        $this->app->initDatabase();
        $this->app->initRedis();
        if ($worker_id == 0) {
            // 仅在第一个 worker 进程开启定时器，避免重复
            $this->initTimer();
        }
    }

    public function open(WebSocket $websocket , Http $http)
    {
        $this->isOpen = true;
    }

    public function close(Server $server , int $fd , int $reacter_id)
    {
        $this->isOpen = false;
        // 销毁
        $identifier = MiscRedis::fdMappingIdentifier($fd);
//        var_dump("离线标识符：{$identifier}");
        if (empty($identifier)) {
            return ;
        }
        $user_id = UserRedis::fdMappingUserId($identifier , $fd);
        // 清除 Redis（删除的太快了）
        $user = User::findById($user_id);
//        var_dump("用户ID：{$user_id}");
//        var_dump("用户是否存在：" . empty($user) . "；用户角色：" . ($user->role ?? '未知的角色'));
        if (!empty($user) && $user->role == 'admin') {
            try {
                DB::beginTransaction();
                $push = [];
                $conn = UserRedis::fdByUserId($identifier , $user->id);
                $conn = array_diff($conn , [$fd]);
//                var_dump("离线用户是否仍然存在在线客户端连接：{$user->id}:，isOnline: " . (empty($conn) ? 'yes' : 'no'));
                if (empty($conn)) {
                    // 如果是客服，用户加入的群组
                    $joined_group = GroupMember::getByUserId($user->id);
                    foreach ($joined_group as $v)
                    {
                        $group_bind_waiter = UserRedis::groupBindWaiter($identifier , $v->group_id);
//                        var_dump("Redis 绑定的数据：" . $group_bind_waiter);
                        $group_bind_waiter = (int) $group_bind_waiter;
                        if ($group_bind_waiter != $user_id) {
//                            var_dump("群：{$v->group_id} 绑定的客服：{$group_bind_waiter} 并非当前退出用户 {$user->id}");
                            // 绑定的并非当前客服
                            continue ;
                        }
                        $user_ids = GroupMember::getUserIdByGroupId($v->group_id);
                        $group_message_id = GroupMessage::insertGetId([
                            'user_id' => $user->id ,
                            'group_id' => $v->group_id ,
                            'type' => 'text' ,
                            'message' => sprintf('系统通知：客服 【%s】已经离线' , $user->username) ,
                        ]);
                        foreach ($user_ids as $v1)
                        {
                            $is_read = $v1 == $user->id ? 'y' : 'n';
                            GroupMessageReadStatus::insert([
                                'user_id' => $v1 ,
                                'group_message_id' => $group_message_id ,
                                'is_read' => $is_read
                            ]);
                        }
                        $msg = GroupMessage::findById($group_message_id);
                        $msg->session_id = Misc::sessionId('group' , $v->group_id);
                        if ($v->group->is_service == 'y' && $msg->user->role == 'admin') {
                            $msg->user->username = '客服 ' . $msg->user->username;
                            $msg->user->nickname = '客服 ' . $msg->user->nickname;
                        }
                        $push[] = [
                            'identifier' => $v->group->identifier ,
                            'user_ids'    => $user_ids ,
                            'type'       => 'group_message' ,
                            'data'       => $msg
                        ];
                    }
                }
                DB::commit();
                foreach ($push as $v)
                {
                    Push::multiple($v['identifier'] , $v['user_ids'] , $v['type'] , $v['data'] , [$fd]);
                }
            } catch(Exception $e) {
                DB::rollBack();
                $exception = (new Throwable())->exceptionJsonHandlerInDev($e , true);
                echo json_encode($exception) . PHP_EOL;
            }
        }
        // 删除 Redis
        $this->clearRedis($user_id , $fd);
    }

    public function message(WebSocket $server , $frame)
    {
        try {
            try {
                $data = json_decode($frame->data , true);
                if (!is_array($data)) {
                    $server->disconnect($frame->fd , 400 , '数据格式不规范，请按照要求提供必要数据');
                    return ;
                }
            } catch(Exception $e) {
                $server->disconnect($frame->fd , 400 , '数据解析异常，请按照要求提供必要数据');
                return ;
            }
            // data 数据格式要求
            $default = [
                // 路由
                'router'    => 'user/test' ,
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
            $instance = new $class($this->websocket , $frame->fd , $data['identifier'] , $data['platform'] , $data['token'] , $data['request']);
            // 执行前置操作
            if (!method_exists($instance , 'before')) {
                throw new Exception("Call to undefined method {$class}::before");
            }
            $next = call_user_func([$instance , 'before']);
            if (!$next) {
                return ;
            }
            // 执行目标操作
            if (!method_exists($instance , $router['method'])) {
                throw new Exception("Call to undefined method {$class}::{$router['method']}");
            }
            call_user_func([$instance , $router['method']] , $data['data']);
            // 执行后置操作
            if (!method_exists($instance , 'after')) {
                throw new Exception("Call to undefined method {$class}::after");
            }
            call_user_func([$instance , 'after']);
            // 由于这个是长连接
            // 我怕他不会自动回收
            // 所以 手动销毁
            unset($instance);
        } catch(Exception $e) {
            $server->push($frame->fd , json_encode([
                'type' => 'error' ,
                'data' => (new Throwable)->exceptionJsonHandlerInDev($e , true)
            ]));
            $server->disconnect($frame->fd , 500 , '服务器发生内部错误，服务器主动切断连接！请反馈错误信息给开发者');
        }
    }

    // 正向接口
    public function request(Http $request , Response $response)
    {
        try {
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
            if (!method_exists($instance , 'before')) {
                throw new Exception("Call to undefined method {$class}::before");
            }
            // 执行前置操作
            $next = call_user_func([$instance , 'before']);
            if (!$next) {
                return ;
            }
            // 执行目标操作
            if (!method_exists($instance , $router['method'])) {
                throw new Exception("Call to undefined method {$class}::{$router['method']}");
            }
            call_user_func([$instance , $router['method']]);
            // 执行后置操作
            if (!method_exists($instance , 'after')) {
                throw new Exception("Call to undefined method {$class}::after");
            }
            call_user_func([$instance , 'after']);
            // 由于这个是长连接
            // 我怕他不会自动回收
            // 所以 手动销毁
            unset($instance);
        } catch(Exception $e) {
            $this->httpResponse($response , (new Throwable)->exceptionJsonHandlerInDev($e , true) , 500);
        }
    }

    public function httpResponse(Response $response , $data = '' , $code = 200)
    {
        $response->header('Content-Type' , 'application/json');
        $response->status(200);
        $response->end(json_encode([
            'code' => $code ,
            'data' => $data ,
        ]));
    }

    public function task(WebSocket $server , $data)
    {

    }

    protected function initTimer()
    {
        Timer::tick(30 * 1000 , function(){
//        Timer::tick(2 * 1000 , function(){
//            var_dump('30s 定时器一直在跑');
            // 清理超过一定时间没有回复的咨询通道
            try {
                DB::beginTransaction();
                $group = Group::serviceGroup();
                $wait_duration = config('app.wait_duration');
                $push = [];
                foreach ($group as $v)
                {
                    $waiter_id = UserRedis::groupBindWaiter($v->identifier , $v->id);
                    if (empty($waiter_id)) {
                        continue ;
                    }
                    $waiter = User::findById($waiter_id);
                    // 检查最近一条消息是否发送超时
                    $last_message = GroupMessage::recentMessage($v->id , 'user');
//                print_r($last_message);
                    if (!empty($last_message)) {
                        $create_time = strtotime($last_message->create_time);
                        $duration = time() - $create_time;
                        if ($duration <= $wait_duration) {
                            continue ;
                        }
                    }
                    UserRedis::delGroupBindWaiter($v->identifier , $v->id);
                    // 群通知：客服已经断开连接！
                    $user_ids = GroupMember::getUserIdByGroupId($v->id);
                    $group_message_id = GroupMessage::insertGetId([
                        'user_id' => $waiter->id ,
                        'group_id' => $v->id ,
                        'type' => 'text' ,
                        'message' => sprintf('系统通知：由于您长时间未回复，客服 【%s】已经离开' , $waiter->username) ,
                    ]);
                    foreach ($user_ids as $v1)
                    {
                        $is_read = $v1 == $waiter->id ? 'y' : 'n';
                        GroupMessageReadStatus::insert([
                            'user_id' => $v1 ,
                            'group_message_id' => $group_message_id ,
                            'is_read' => $is_read
                        ]);
                    }
                    $msg = GroupMessage::findById($group_message_id);
                    $msg->session_id = Misc::sessionId('group' , $v->id);
                    if ($v->is_service == 'y' && $msg->user->role == 'admin') {
                        $msg->user->username = '客服 ' . $msg->user->username;
                        $msg->user->nickname = '客服 ' . $msg->user->nickname;
                    }
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
                    Push::multiple($v['identifier'] , $v['user_ids'] , $v['type'] , $v['data']);
                }
            } catch(Exception $e) {
                DB::rollBack();
            }
        });

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
                    $temp_user = User::getTempByTimestamp($timestamp);
                    $clear_group = function($group_id){
                        // 清理临时群
                        Group::destroy($group_id);
                        // 清理临时群中的成员
                        GroupMember::delByGroupId($group_id);
                        // 群聊消息
                        GroupMessage::delByGroupId($group_id);
                        // 清理群聊已读/未读
                        GroupMessageReadStatus::delByGroupId($group_id);
                    };
                    if (!empty($temp_user)) {
                        /**
                         * **************
                         * 清理临时用户
                         * **************
                         */
                        foreach ($temp_user as $v)
                        {
                            User::destroy($v->id);
                            GroupMember::delByUserId($v->id);
                            $group_ids = GroupMember::getGroupIdByUserId($v->id);
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
                    $temp_group = Group::getTempByTimestamp($timestamp);
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

    }

    // 清理 Redis
    public function clearRedis($user_id , $fd = null)
    {
        $user = User::findById($user_id);
        if (empty($user)) {
//            var_dump("用户表中未找到用户id = {$user_id} 的用户");
            return ;
        }
        UserRedis::delNumberOfReceptionsForWaiter($user->identifier , $user->id);
        if (empty($fd)) {
            $fds = $fds = UserRedis::fdByUserId($user->identifier , $user->id);
        } else {
            $fds = [$fd];
        }
//        var_dump("用户产生的客户端连接ID：" . json_encode($fds));
        foreach ($fds as $v)
        {
            UserRedis::delFdByUserId($user->identifier , $user_id , $v);
            UserRedis::delFdMappingUserId($user->identifier , $v);
            MiscRedis::delfdMappingIdentifier($v);
        }
        // todo 删除用户加入过的活跃群组
        if (!UserRedis::isOnline($user->identifier , $user->id)) {
            // 确定当前已经处于完全离线状态，那么删除掉该用户绑定的相关信息
            $group_ids = GroupMember::getGroupIdByUserId($user->id);
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

    public function run()
    {
        return $this->websocket->start();
    }
}