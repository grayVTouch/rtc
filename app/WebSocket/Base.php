<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/10
 * Time: 16:45
 */

namespace App\WebSocket;

use App\Util\Push;
use Swoole\WebSocket\Server as WebSocket;

class Base implements BaseInterface
{
    protected $conn = null;

    protected $fd = null;

    protected $identifier = null;

    protected $platform = null;

    protected $token = null;

    protected $request = null;

    public function __construct(WebSocket $conn , $fd , string $identifier = '' , string $platform = '' , string $token = '' , string $request = '')
    {
        $this->fd       = $fd;
        $this->identifier = $identifier;
        $this->platform = $platform;
        $this->token    = $token;
        $this->request = $request;
        $this->conn     = $conn;
    }

    // 前置操作
    public function before() :bool
    {
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
        return self::response($$data , $code);
    }

    // 响应：自定义
    public function response($data = '' , int $code = 200)
    {
        return $this->conn->push($this->fd , json($code , $data , $this->request , 'response'));
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