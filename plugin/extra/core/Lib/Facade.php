<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/1
 * Time: 10:35
 *
 * 前缀 u_
 */

namespace Core\Lib;

use Exception;

class Facade implements FacadeInterface
{
    // 实例集合
    private static $instance = [];

    // 注册
    public static function register(string $key , $value)
    {
        self::$instance[$key] = $value;
    }

    // 检查 key 是否存在
    public static function u_exist(string $key)
    {
        return isset(self::$instance[$key]);
    }

    // 获取实例
    public static function u_make(string $key)
    {
        return self::$instance[$key] ?? null;
    }

    public static function getFacadeAccessor(): string
    {
        throw new Exception('您尚未实现该方法！请重新实现该方法');
    }

    // 调用实例方法
    public static function __callStatic($name , array $args)
    {
        $key = static::getFacadeAccessor();
        if (!self::u_exist($key)) {
            throw new Exception("注册表中未找到 {$key} 对应的实例");
        }
        $ins = self::u_make($key);
        return call_user_func_array([$ins , $name] , $args);
    }
}