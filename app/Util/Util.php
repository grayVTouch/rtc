<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/25
 * Time: 14:38
 */

namespace App\Util;


class Util
{
    // 成功
    public static function success($data = '' , $code = 200)
    {
        return [
            'code' => $code ,
            'data' => $data
        ];
    }

    public static function error($data = '' , $code = 400)
    {
        return [
            'code' => $code ,
            'data' => $data ,
        ];
    }
}