<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/9
 * Time: 17:15
 */

use function core\format_path;
use Engine\Facade\Log;
use function extra\config as config_function;

/**
 * 获取配置值
 *
 * @param string $key 配置文件名称
 * @param array $args
 * @return mixed|string
 * @throws Exception
 */
function config(string $key , array $args = []){
    $dir = __DIR__ . '/../config';
    return config_function($dir , $key , $args);
}

/**
 * websocket 失败时的响应
 *
 * @param string $request 请求
 * @param string $data
 * @param int    $code 错误码
 * @return false|string
 */
function error(string $request , $data = '' , int $code = 400)
{
    return json($code , $data , $request);
}

/**
 * 成功时的 websocket 响应
 *
 * @param string $request 请求id
 * @param string $data    响应数据
 * @param int $code       代码
 * @return false|string
 */
function success(string $request , $data = '' , int $code = 200)
{
    return json($code , $data , $request , 'response');
}

/**
 * websocket 响应数据
 *
 * @param int    $code
 * @param mixed  $data
 * @param string $request
 * @param string $type
 * @return array
 */
function response(int $code , $data , string $request = '' , string $type = 'response')
{
    $data = compact('code' , 'data');
    return compact('type' , 'request' , 'data');
}

/**
 * json 数据
 *
 * @param int   $code
 * @param mixed $data
 * @param string $request
 * @param string $type
 * @return false|string
 */
function json(int $code , $data , string $request = '' , string $type = 'response')
{
    return json_encode(response($code , $data , $request , $type));
}

/**
 * http 响应数据
 *
 * @param int   $code
 * @param mixed $data
 * @return false|string
 */
function json_for_http(int $code , $data)
{
    return json_encode(compact('code' , 'data'));
}

/**
 * 生成完整的资源访问地址
 *
 * @param string $path
 * @return string
 * @throws Exception
 */
function res_url(string $path = '')
{
    if (empty($path)) {
        return '';
    }
    $url = config('app.host');
    $url = format_path($url);
    return sprintf('%s%s' , $url , $path);
}

// 记录错误日志
function log($msg , $flag = 'runtime')
{
    return Log::write(sprintf('[%s] %10s %s' , date('Y-m-d H:i:s') , $flag , $msg));
}