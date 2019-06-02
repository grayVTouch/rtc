<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/9
 * Time: 17:15
 */

use function core\format_path;
use Core\Lib\Container;
use function core\ssl_random;
use function extra\config as config_function;

function config($key , array $args = []){
    $dir = __DIR__ . '/../config';
    return config_function($dir , $key , $args);
}

function redis()
{
    return Container::make('redis');
}

function error($request , $data = '' , $code = 400)
{
    return json($code , $data , $request);
}

function success($request , $data = '' , $code = 200)
{
    return json($code , $data , $request , 'response');
}

function response($code , $data , $request = '' , $type = 'response')
{
    $data = compact('code' , 'data');
    return compact('type' , 'request' , 'data');
}

function json($code , $data , $request = '' , $type = 'response')
{
    return json_encode(response($code , $data , $request , $type));
}

function json_for_http($code , $data)
{
    return json_encode(compact('code' , 'data'));
}

function identifier()
{
    return ssl_random(32);
}

function token()
{
    return ssl_random(255);
}

// 生成资源访问地址
function res_url($path = '')
{
    if (empty($path)) {
        return '';
    }
    $url = config('app.url');
    $url = format_path($url);
    return sprintf('%s%s' , $url , $path);
}