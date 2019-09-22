<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/11
 * Time: 12:28
 */

namespace App\WebSocket;

use App\WebSocket\Action\UserAction;

class User extends Auth
{
    // 获取平台咨询通道信息
    public function groupForAdvoise(array $param)
    {
        $res = UserAction::groupForAdvoise($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    public function info()
    {
        return self::success($this->user);
    }

    // 与我相关的申请记录
    public function app(array $param)
    {
        $param['limit'] = $param['limit'] ?? '';
        $param['order'] = $param['order'] ?? '';
        $res = UserAction::app($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 编辑信息
    public function editUserInfo(array $param)
    {
        $param['nickname']  = $param['nickname'] ?? '';
        $param['avatar']    = $param['avatar'] ?? '';
        $param['sex']       = $param['sex'] ?? '';
        $param['birthday']  = $param['birthday'] ?? '';
        $param['signature'] = $param['signature'] ?? '';
        $res = UserAction::editUserInfo($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }
}