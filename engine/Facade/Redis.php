<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/10
 * Time: 14:00
 */

namespace Engine\Facade;

use Core\Lib\Facade;

/**
 * @see \Core\Lib\Redis
 *
 * @method static native(string $method , ...$args)
 * @method static key(string $name)
 * @method static string(string $name , string $value = '' , int $timeout = 0)
 * @method static hash(string $name , string $key , string $value = '' , int $timeout = 0)
 * @method static hashAll(string $name , array $data = [] , int $timeout = 0)
 * @method static lPush(string $name , string $value)
 * @method static rPush(string $name , string $value)
 * @method static lRange(string $name , int $start = 0 , int $end = -1)
 * @method static del($name)
 * @method static parse(string $str = '')
 * @method static json($obj = null)
 * @method static flushAll()
 * @method static setAll(string $name , array $data = [] , int $timeout = 0)
 * @method static sAdd(string $name , $value , int $timeout = 0)
 * @method static sRem(string $name , $value)
 * @method static sIsMember(string $name , $value)
 * @method static lPop(string $name)
 * @method static rPop(string $name)
 *
 */
class Redis extends Facade
{
    /**
     * 获取实例 key
     *
     * @return string
     */
    public static function getFacadeAccessor(): string
    {
        return 'redis';
    }
}