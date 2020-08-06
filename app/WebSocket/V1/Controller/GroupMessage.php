<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/20
 * Time: 10:50
 */

namespace App\WebSocket\V1\Controller;


use App\WebSocket\V1\Action\GroupMessageAction;
use App\WebSocket\V1\Action\UserAction;

class GroupMessage extends Auth
{
    // 重置群未读消息数量
    public function resetUnread(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $res = GroupMessageAction::resetUnread($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 设置单条消息已读/未读（所有类型消息）
    public function readed(array $param)
    {
        $param['group_message_id'] = $param['group_message_id'] ?? '';
        $res = GroupMessageAction::readed($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    //  群：历史消息记录
    public function history(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $param['limit_id'] = $param['limit_id'] ?? '';
        $param['limit'] = $param['limit'] ?? '';
        $res = GroupMessageAction::history($this, $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'], $res['code']);
        }
        return $this->success($res['data']);
    }

    // 最新消息
    public function lastest(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $param['limit_id'] = $param['limit_id'] ?? '';
        $param['limit'] = $param['limit'] ?? '';
        $res = GroupMessageAction::lastest($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    public function earliest(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $param['limit_id'] = $param['limit_id'] ?? '';
        $param['limit'] = $param['limit'] ?? '';
        $res = GroupMessageAction::earliest($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 重置未读数量
    public function delete(array $param)
    {
        $param['group_message_id'] = $param['group_message_id'] ?? '';
        $res = GroupMessageAction::delete($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 消息撤回
    public function withdraw(array $param)
    {
        $param['group_message_id'] = $param['group_message_id'] ?? '';
        $res = GroupMessageAction::withdraw($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 消息转发-逐条转发
    public function serialForward(array $param)
    {
        $param['type'] = $param['type'] ?? '';
        $param['target_id'] = $param['target_id'] ?? '';
        $param['group_message_id'] = $param['group_message_id'] ?? '';
        $res = GroupMessageAction::serialForward($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 消息转发-合并转发
    public function mergeForward(array $param)
    {
        $param['type'] = $param['type'] ?? '';
        $param['target_id'] = $param['target_id'] ?? '';
        $param['group_message_id'] = $param['group_message_id'] ?? '';
        $res = GroupMessageAction::mergeForward($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 群聊消息-同步（用于同步app本地数据库和线上数据库）
    public function sync(array $param)
    {
        $param['id_list'] = $param['id_list'] ?? '';
        $res = GroupMessageAction::sync($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

}