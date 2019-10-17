<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/20
 * Time: 11:51
 */

namespace App\WebSocket;


use App\WebSocket\Action\SessionAction;

class Session extends Auth
{
    // 会话列表
    public function session(array $param)
    {
        $res = SessionAction::session($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 置顶会话
    public function top(array $param)
    {
        $param['type'] = $param['type'] ?? '';
        $param['target_id'] = $param['target_id'] ?? '';
        $param['top'] = $param['top'] ?? '';
        $res = SessionAction::top($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // todo 消息免打扰

    // 创建并更新
    public function createOrUpdate(array $param)
    {
        $param['type']      = $param['type'] ?? '';
        $param['target_id'] = $param['target_id'] ?? '';
        $param['top']       = $param['top'] ?? '';
        $res = SessionAction::createOrUpdate($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }
}