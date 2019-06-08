<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/1
 * Time: 10:35
 *
 *
 */

namespace Core\Lib;

use Exception;

class Facade implements FacadeInterface
{
    // 实例集合
    private static $instance = [];

    // 设置
    public static function set(string $key , $value)
    {
        self::$instance[$key] = $value;
    }

    // 检查 key 是否存在
    public static function exist(string $key)
    {
        return isset(self::$instance[$key]);
    }

    // 获取实例
    public static function get(string $key)
    {
        return self::$instance[$key] ?? null;
    }

    public static function getFacadeAccessor(): string
    {
        throw new Exception('请重新实现该方法');
    }

    // 调用实例方法
    public static function __callStatic($name , array $args)
    {
        return call_user_func_array(self::$instance[self::getFacadeAccessor()] , $args);
    }
}