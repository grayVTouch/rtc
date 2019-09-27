<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 9:50
 */

namespace App\Http\Action;

class Action
{
    public static function success($data = '' , $code = 200)
    {
        return compact('data' , 'code');
    }

    public static function error($data = '' , $code = 400)
    {
        return compact('data' , 'code');
    }
}