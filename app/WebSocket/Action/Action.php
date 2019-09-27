<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 9:39
 */

namespace App\WebSocket\Action;

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