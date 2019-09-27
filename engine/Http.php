<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/2
 * Time: 11:46
 */

namespace Engine;

use Swoole\Http\Server as BaseHttp;
use Swoole\Http\Request;
use Swoole\Http\Response;

class Http
{
    /**
     * @see Swoole\Http\Server
     */
    protected $http;

    public function __construct()
    {
        $config = config('app.http');
        $http = new BaseHttp($config['ip'], $config['port']);
        $http->set([
            // web 根目录
            'document_root' => config('app.web_dir') ,
            // 启用静态资源处理，必须配套使用
            'enable_static_handler' => true,
        ]);
        $http->on('request' , function(Request $request, Response $response){
            $response->end('禁止操作');
        });
        $http->start();
    }
}