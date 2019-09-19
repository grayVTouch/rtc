<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/14
 * Time: 23:18
 */

namespace App\Util;


class DataUtil
{
    // 没有客服
    public static function noCustomerService($group , string $message = '')
    {
        return [
            'group'     => $group ,
            'message'   => $message
        ];
    }

    // token
    public static function token($token = '')
    {
        return $token;
    }
}