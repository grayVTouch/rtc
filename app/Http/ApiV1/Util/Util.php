<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/1
 * Time: 12:04
 */

namespace App\Http\ApiV1\Util;


class Util
{
    public static function success($data = '' , int $code = 0)
    {
        return compact('code' , 'data');
    }

    public static function error($data = '' , int $code = 400)
    {
        return compact('code' , 'data');
    }
}