<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/12
 * Time: 14:13
 */

namespace App\Util;


class TimerLogUtil extends Util
{
    public static function logCheck(callable $callback)
    {
        if (!config('app.enable_timer_log')) {
            return ;
        }
        call_user_func($callback);
    }
}