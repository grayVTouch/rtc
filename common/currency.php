<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/9
 * Time: 17:15
 */

use Core\Lib\Container;
use function core\ssl_random;
use function extra\config as config_function;

function config($key , array $args = []){
    $dir = __DIR__ . '/../config';
    return config_function($dir , $key , $args);
}

function app()
{
    return Container::make('app');
}

function redis()
{
    return Container::make('redis');
}

function database()
{
    return Container::make('database');
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
    return compact('type' , 'request' , 'code' , 'data');
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