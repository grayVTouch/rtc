<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/10
 * Time: 16:45
 */

namespace App\Http;

use App\Redis\MiscRedis;
use App\Util\Push;
use Swoole\WebSocket\Server as WebSocket;
use Swoole\Http\Request;
use Swoole\Http\Response;
use App\Model\Project;

class Base implements BaseInterface
{
    public $conn = null;

    public $identifier = null;

    public $request = null;

    public $response = null;

    public function __construct(WebSocket $conn , Request $request , Response $response , string $identifier = '')
    {
        $this->identifier = $identifier;
        $this->request = $request;
        $this->response = $response;
        $this->conn     = $conn;
    }

    // 前置操作
    public function before() :bool
    {
        // 检查 identifier 是否正确！！
        if (empty(Project::findByIdentifier($this->identifier))) {
            $this->error('identifier 不正确！！请先创建项目！' , 400);
            return false;
        }
        return true;
    }

    // 后置操作
    public function after() :void
    {

    }

    // 响应：成功时
    public function success($data = '' , $code = 200)
    {
        return self::response($data , $code);
    }

    // 响应：失败时
    public function error($data = '' , $code = 400)
    {
        return self::response($data , $code);
    }

    // 响应：自定义
    public function response($data = '' , int $code = 200)
    {
        // 设置响应头
        $this->response->header('Content-Type' , 'application/json');
        $this->response->status(200);
        return $this->response->end(json_for_http($code , $data));
    }

    // 结合当前业务的发送接口：发送单条数据
    public function send(int $user_id , string $type = '' , array $data = [])
    {
        return $this->push($user_id , $type , $data , [$this->fd]);
    }

    // 结合当前业务的发送接口：发送多条数据
    public function sendAll(array $user_ids , string $type = '' , array $data = [])
    {
        return $this->pushAll($user_ids , $type , $data , [$this->fd]);
    }

    // 单条推送：推送其他数据
    public function push($user_id , string $type = '' , array $data = [] , array $exclude = [])
    {
        return Push::single($this->identifier , $user_id , $type , $data , $exclude);
    }

    // 单条推送：推送其他数据
    public function pushAll($user_ids , string $type = '' , array $data = [] , array $exclude = [])
    {
        return Push::multiple($this->identifier , $user_ids , $type , $data , $exclude);
    }
}