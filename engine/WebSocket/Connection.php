<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/10
 * Time: 17:22
 */

namespace Engine\WebSocket;

use App\WebSocket\User;
use Core\Lib\Container;
use Engine\Application;
use Exception;
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

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->config       = config('app.websocket');
        $this->initialize();
    }

    protected function initialize()
    {
        $this->websocket = $ws = new Websocket($this->config['ip'] , $this->config['port']);
        Container::bind('websocket' , $ws);
        // 设置进程数量
        $ws->set([
            'task_worker_num'   => $this->config['task_worker'] ,
            'worker_num'        => $this->config['worker'] ,
            'enable_reuse_port' => $this->config['reuse_port'] ,
        ]);
        $ws->on('WorkerStart' , function(...$args){
            call_user_func_array([$this , 'workerStart'] , $args);
        });
        // 子进程内部调用
        $ws->on('open' , [$this , 'open']);
        $ws->on('close' , [$this , 'close']);
        $ws->on('task' , [$this , 'task']);
        $ws->on('message' , [$this , 'message']);
        $ws->on('request' , [$this , 'request']);
        $ws->start();
    }

    public function workerStart(WebSocket $websocket , int $worker_id)
    {
        $this->app->initDatabase();
        $this->app->initRedis();
    }

    public function open(WebSocket $websocket , Http $http)
    {
        $this->isOpen = true;
    }

    public function close()
    {
        $this->isOpen = false;
    }

    public function message(WebSocket $server , $frame)
    {
        try {
            $data = json_decode($frame->data , true);
            if (!is_array($data)) {
                $this->disconnect($frame->fd , 400 , '数据格式不规范，请按照要求提供必要数据');
                return ;
            }
        } catch(Exception $e) {
            $this->websocket->disconnect($frame->fd , 400 , '数据解析异常，请按照要求提供必要数据');
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
        // 实例化对象
        $instance = new $class($this->websocket , $frame->fd , $data['identifier'] , $data['platform'] , $data['token'] , $data['request']);
        // 执行前置操作
        $next = call_user_func([$instance , 'before']);
        if (!$next) {
            return ;
        }
        // 执行目标操作
        call_user_func([$instance , $router['method']] , $data['data']);
        // 执行后置操作
        call_user_func([$instance , 'after']);
        // 由于这个是长连接
        // 我怕他不会自动回收
        // 所以 手动销毁
        unset($instance);
    }

    // 正向接口
    public function request(Http $request , Response $response)
    {
        $router = $this->parseRouter($request->server['request_uri']);
        if (empty($router)) {
            $response->header('Content-Type' , 'application/json');
            $response->status(200);
            $response->end(json_encode([
                'code' => 400 ,
                'data' => '请求的地址不正确' ,
            ]));
            return ;
        }
        $param = $request->post;
        $param['identifier'] = $param['identifier'] ?? '';
        $namespace = 'App\Http\\';
        $class = sprintf('%s%s' , $namespace , $router['class']);
        // 实例化对象
        $instance = new $class($this->websocket , $request , $response , $param['identifier']);
        // 执行前置操作
        $next = call_user_func([$instance , 'before']);
        if (!$next) {
            return ;
        }
        // 执行目标操作
        call_user_func([$instance , $router['method']]);
        // 执行后置操作
        call_user_func([$instance , 'after']);
        // 由于这个是长连接
        // 我怕他不会自动回收
        // 所以 手动销毁
        unset($instance);
    }

    public function task(WebSocket $server , $data)
    {

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
}

// 私聊（同区域）
// 群聊（同区域）
// 聊天室（不同区域！！！）