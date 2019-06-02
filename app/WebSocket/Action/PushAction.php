<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/22
 * Time: 10:48
 */

namespace App\WebSocket\Action;


use App\Model\Push;
use App\WebSocket\Auth;

class PushAction extends Action
{
    public static function unread(Auth $auth , array $param)
    {
        $res = Push::unread($auth->user->id , config('app.limit'));
        return self::success($res);
    }
}