<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 21:54
 */

namespace App\WebSocket\Action;


use App\Model\Group;
use App\WebSocket\Auth;

class UserAction extends Action
{
    // 咨询通道绑定的群信息
    public static function groupForAdvoise(Auth $auth , array $param)
    {
        $group = Group::advoiseGroupByUserId($auth->user->id);
        return self::success($group);
    }
}