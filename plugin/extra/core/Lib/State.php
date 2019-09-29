<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/10
 * Time: 16:50
 */

namespace Core\Lib;

use Exception;

class State
{
    protected static $state = [];

    public static function make(string $key = '' , $value = null)
    {
        if (empty($value)) {
            return self::$state[$key] ?? false;
        }
        self::$state[$key] = $value;
    }

}