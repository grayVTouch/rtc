<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/10
 * Time: 16:45
 */

namespace App\Http\Web;

use App\Redis\MiscRedis;
use App\Util\PushUtil;
use Swoole\WebSocket\Server as WebSocket;
use Swoole\Http\Request;
use Swoole\Http\Response;
use App\Model\ProjectModel;

class Common
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
        // 加载极验验证
        require_once __DIR__ . '/../../../plugin/gt3/lib/class.geetestlib.php';
        return true;
    }

    // 后置操作
    public function after() :void
    {

    }

    // 响应：成功时
    public function success($data = '' , $code = 0)
    {
        return self::response($data , $code);
    }

    // 响应：失败时
    public function error($data = '' , $code = 400)
    {
        return self::response($data , $code);
    }

    // 响应：自定义
    public function response($data = '' , int $code = 0)
    {
        // 设置响应头
        $this->response->header('Content-Type' , 'application/json');

        // 允许跨域
        $this->response->header('Access-Control-Allow-Origin' , '*');
        $this->response->header('Access-Control-Allow-Methods' , 'GET,POST,PUT,PATCH,DELETE');
        $this->response->header('Access-Control-Allow-Credentials' , 'false');
        $this->response->header('Access-Control-Allow-Headers' , 'Authorization,Content-Type,X-Request-With,Ajax-Request');

        $this->response->status(200);
        return $this->response->end(json_for_http($code , $data));
    }

    // 响应：自由的响应方式
    public function rawResponse(string $data = '')
    {
        // 设置响应头
        $this->response->header('Content-Type' , 'application/json');
        // 允许跨域
        $this->response->header('Access-Control-Allow-Origin' , '*');
        $this->response->header('Access-Control-Allow-Methods' , 'GET,POST,PUT,PATCH,DELETE');
        $this->response->header('Access-Control-Allow-Credentials' , 'false');
        $this->response->header('Access-Control-Allow-Headers' , 'Authorization,Content-Type,X-Request-With,Ajax-Request');
        $this->response->status(200);
        return $this->response->end($data);
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
        return PushUtil::single($this->identifier , $user_id , $type , $data , $exclude);
    }

    // 单条推送：推送其他数据
    public function pushAll($user_ids , string $type = '' , array $data = [] , array $exclude = [])
    {
        return PushUtil::multiple($this->identifier , $user_ids , $type , $data , $exclude);
    }
}