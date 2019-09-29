<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/10
 * Time: 16:50
 */

namespace Core\Lib;

use Exception;

class Container
{

    protected static $register = [];

    public static function bind(string $abstract = '' , $value = null)
    {
        static::$register[$abstract] = $value;
    }

    public static function make(string $abstract = '' , ...$args)
    {
        if (!isset(static::$register[$abstract])) {
            return null;
        }
        if (is_callable(static::$register[$abstract])) {
            return call_user_func_array($abstract , $args);
        }
        return static::$register[$abstract];
    }

    public static function singleton(string $abstract = '' , $value = null)
    {
        if (array_key_exists($abstract , static::$register)) {
            throw new Exception('单例模式，不允许存在多个实例');
        }
        self::bind($abstract , $value);
    }

}