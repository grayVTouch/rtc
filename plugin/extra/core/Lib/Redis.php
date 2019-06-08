<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2018/12/12
 * Time: 10:19
 */
namespace Core\Lib;

use Redis as ORedis;
use Exception;

class Redis
{
    // 连接
    protected $conn = null;
    // 安全前缀
    protected $prefix = '';

    public function __construct(array $option = [])
    {
        $option['host'] = $option['host'] ?? '';
        $option['port'] = $option['port'] ?? '';
        $option['password'] = $option['password'] ?? '';
        $option['timeout'] = $option['timeout'] ?? '';
        $option['prefix'] = $option['prefix'] ?? '';

        $this->conn = new ORedis();
        $res = $this->conn->connect($option['host'] , $option['port'] , $option['timeout']);
        if (!$res) {
            throw new Exception("创建 Redis 连接发生错误：host：{$option['host']}；port：{$option['port']}；timeout：{$option['timeout']}");
        }
        $res = $this->conn->auth($option['password']);
        if (!$res) {
            throw new Exception('Redis 密码错误，认证失败');
        }
        $this->prefix = $option['prefix'];
    }


    // 调用原生方法
    public function native($method , ...$args)
    {
        return call_user_func_array([$this->conn , $method] , $args);
    }

    // 生成带有前缀的 key
    public function key($name)
    {
        return sprintf('%s%s' , $this->prefix , $name);
    }

    // 获取/设置字符串
    public function string($name , $value = '' , $timeout = 0)
    {
        $key = $this->key($name);
        if (empty($value)) {
            return $this->native('get' , $key);
        }
        $this->native('set' , $key , $value);
        if (!empty($timeout)) {
            $this->native('expire' , $key , $timeout);
        }
    }

    // 获取/设置字符串
    public function hash($name , $key = '' , $value = '' , $timeout = 0)
    {
        $name = $this->key($name);
        if (empty($value)) {
            return $this->native('hGet' , $name , $key);
        }
        $this->native('hSet' , $name , $key , $value);
        if (!empty($timeout)) {
            $this->native('expire' , $name , $timeout);
        }
    }

    // 获取/设置字符串
    public function hashAll($name , array $data = [] , $timeout = 0)
    {
        $name = $this->key($name);
        if (empty($data)) {
            return $this->native('hGetAll' , $name);
        }
        $this->native('hMSet' , $name , $data);
        if (!empty($timeout)) {
            $this->native('expire' , $name , $timeout);
        }
    }

    public function lPush($name , $value)
    {
        $key = $this->key($name);
        return $this->native('lPush' , $key , $value);
    }

    public function rPush($name , $value)
    {
        $key = $this->key($name);
        return $this->native('rPush' , $key , $value);
    }

    public function lRange($name , int $start = 0 , int $end = -1)
    {
        $key = $this->key($name);
        return $this->native('lRange' , $key , $start , $end);
    }

    // 删除 key
    public function del($name)
    {
        return $this->native('del' , $this->key($name));
    }

    public function parse($str = '')
    {
        return json_decode($str , true);
    }

    public function json($obj = null)
    {
        return json_encode($obj);
    }

    // 清空 key
    public function  flushAll()
    {
        $key = sprintf('%s*' , $this->prefix);
        $keys = $this->native('keys' , $key);
        return $this->native('del' , $keys);
    }
}