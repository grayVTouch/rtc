<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/2/10
 * Time: 10:23
 */

namespace Engine\Facade;

use Core\Lib\Facade;

/**
 * @see /Engine/Application
 *
 * @method static getPath(string $key)
 * @method static setPath(string $key , string $path)
 */

class Application extends Facade
{
    /**
     * 获取实例 key
     *
     * @return string
     */
    public static function getFacadeAccessor(): string
    {
        return 'app';
    }
}