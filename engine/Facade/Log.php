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
 * @method static write($data , string $mode = 'a')
 *
 */
class Log extends Facade
{
    /**
     * 获取实例 key
     *
     * @return string
     */
    public static function getFacadeAccessor(): string
    {
        return 'log';
    }
}