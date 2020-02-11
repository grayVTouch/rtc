<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/10
 * Time: 16:45
 */

namespace App\WebSocket\V1\Controller;

use App\WebSocket\V1\Model\ProjectModel;
use App\WebSocket\V1\Redis\MiscRedis;
use App\WebSocket\V1\Util\PushUtil;
use Core\Lib\Facade;
use Engine\Facade\Application;
use Swoole\WebSocket\Server as WebSocket;

class Base
{
    public $conn = null;

    public $fd = null;

    public $identifier = null;

    public $platform = null;

    public $token = null;

    public $request = null;

    public $debug = null;

    public $userId = null;

    public function __construct(WebSocket $conn , $fd , string $identifier = '' , string $platform = '' , string $token = '' , string $request = '' , $user_id = '' , $debug = '')
    {
        $this->fd       = $fd;
        $this->identifier = $identifier;
        $this->platform = $platform;
        $this->token    = $token;
        $this->request = $request;
        $this->conn     = $conn;
        $this->userId = $user_id;
        $this->debug = $debug;
    }

    // 前置操作
    public function before() :bool
    {
        // 检查 identifier 是否正确！！
        if (empty(ProjectModel::findByIdentifier($this->identifier))) {
            $this->error('identifier 不正确！！请先创建项目！' , 400);
            return false;
        }
        MiscRedis::fdMappingIdentifier($this->fd , $this->identifier);

        // 插件目录
        $plugin_dir = Application::getPath('plugin');

        // 自定义的一些代码
        require_once __DIR__ . '/../Common/currency.php';
        // 加载图形验证码插件
        require_once $plugin_dir . '/captcha/vendor/autoload.php';

        // 加载极验验证
        require_once $plugin_dir . '/gt3/lib/class.geetestlib.php';
        require_once $plugin_dir . '/gt3/config/config.php';

        // 加载二维码
        require_once $plugin_dir . '/qrcode/vendor/autoload.php';

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
        return $this->conn->push($this->fd , json($code , $data , $this->request , 'response'));
    }

    // 针对当前连接进行推送
    public function clientPush($client_id , $type , $data = '')
    {
        return $this->conn->push($client_id , json_encode(compact('type' , 'data')));
    }

    // 结合当前业务的发送接口：发送单条数据
    public function send(int $user_id , string $type = '' , $data = [])
    {
        return $this->push($user_id , $type , $data , [$this->fd]);
    }

    // 结合当前业务的发送接口：发送多条数据
    public function sendAll(array $user_ids , string $type = '' , $data = [])
    {
        return $this->pushAll($user_ids , $type , $data , [$this->fd]);
    }

    // 单条推送：推送其他数据
    public function push($user_id , string $type = '' , $data = [] , array $exclude = [])
    {
        return PushUtil::single($this->identifier , $user_id , $type , $data , $exclude);
    }

    // 单条推送：推送其他数据
    public function pushAll($user_ids , string $type = '' , $data = [] , array $exclude = [])
    {
        return PushUtil::multiple($this->identifier , $user_ids , $type , $data , $exclude);
    }
}