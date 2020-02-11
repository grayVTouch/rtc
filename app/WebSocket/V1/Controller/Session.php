<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/20
 * Time: 11:51
 */

namespace App\WebSocket\V1\Controller;


use App\WebSocket\V1\Action\SessionAction;

class Session extends Auth
{
    // 会话列表
    public function session(array $param)
    {
        $param['page'] = $param['page'] ?? '';
        $param['limit'] = $param['limit'] ?? '';
        $res = SessionAction::session($this , $param);
        if ($res['code'] != 0) {
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
        if ($res['code'] != 0) {
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
        $res = SessionAction::createOrUpdate($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 删除会话
    public function delete(array $param)
    {
        $param['id_list'] = $param['id_list'] ?? '';
        $res = SessionAction::delete($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 加入聊天室
    public function sessionProcess(array $param)
    {
        $param['type'] = $param['type'] ?? '';
        $param['target_id'] = $param['target_id'] ?? '';
        $res = SessionAction::sessionProcess($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 清空会话内当前产生的聊天记录
    public function emptyPrivateHistory(array $param)
    {
        $param['chat_id'] = $param['chat_id'] ?? '';
        $res = SessionAction::emptyPrivateHistory($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 清空会话内当前产生的聊天记录
    public function emptyGroupHistory(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $res = SessionAction::emptyGroupHistory($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 设置会话背景
    public function setSessionBackground(array $param)
    {
        $param['type']      = $param['type'] ?? '';
        $param['target_id'] = $param['target_id'] ?? '';
        $param['background'] = $param['background'] ?? '';
        $res = SessionAction::setSessionBackground($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

}