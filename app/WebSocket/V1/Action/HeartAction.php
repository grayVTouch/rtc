<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/26
 * Time: 9:37
 */

namespace App\WebSocket\V1\Action;


use App\WebSocket\V1\Controller\Base;

class HeartAction extends Action
{
    public static function ping(Base $base , array $param)
    {
        return self::success('pong');
    }
}