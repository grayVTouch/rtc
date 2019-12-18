<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/22
 * Time: 10:46
 */

namespace App\WebSocket;


use App\WebSocket\Action\PushAction;

class Push extends Auth
{
    // 更新单条推送读取状态
    public function updateIsRead(array $param)
    {
        $param['push_id'] = $param['push_id'] ?? '';
        $param['is_read'] = $param['is_read'] ?? '';
        $res = PushAction::updateIsRead($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 获取未读推送消息（总是获取给定数量的未读消息数量）
    public function unread(array $param)
    {
        $res = PushAction::unread($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 我的推送
    public function myPush(array $param)
    {
        $param['type']      = $param['type'] ?? '';
        $param['limit']     = $param['limit'] ?? '';
        $param['limit_id']  = $param['limit_id'] ?? '';
        $res = PushAction::myPush($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 重置某类型推送的读取状态
    public function resetUnread(array $param)
    {
        $param['type'] = $param['type'] ?? '';
        $res = PushAction::resetUnread($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

}