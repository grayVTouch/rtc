<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 21:54
 */

namespace App\WebSocket\Action;


use App\Model\ApplicationModel;
use App\Model\GroupModel;
use App\WebSocket\Auth;
use function WebSocket\ws_config;

class UserAction extends Action
{
    // 咨询通道绑定的群信息
    public static function groupForAdvoise(Auth $auth , array $param)
    {
        $group = GroupModel::advoiseGroupByUserId($auth->user->id);
        return self::success($group);
    }

    // 申请记录
    public static function app(Auth $auth , array $param)
    {
        $param['user_id'] = $auth->user->id;
        $param['limit'] = empty($param['limit']) ? ws_config('app.limit') : $param['limit'];
        $order = parse_order($param['order']);
        $res = ApplicationModel::list($param , $order , $param['limit']);
        return self::success($res);
    }
}