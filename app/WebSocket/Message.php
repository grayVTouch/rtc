<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 21:32
 */

namespace App\WebSocket;


use App\WebSocket\Action\MessageAction;


class Message extends Auth
{
    // 未读消息（私聊 + 群聊）
    public function unreadCountForCommunication(array $param)
    {
        $res = MessageAction::unreadCountForCommunication($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 推送消息
    public function unreadCountForPush(array $param)
    {
        $res = MessageAction::unreadCountForPush($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 总：通迅消息 + 推送消息 + 申请消息
    public function unreadCount(array $param)
    {
        $res = MessageAction::unreadCount($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 历史记录
    public function history(array $param)
    {
//        var_dump("message history do it");
        
        $param['friend_id'] = $param['friend_id'] ?? '';
        $param['limit_id'] = $param['limit_id'] ?? '';
        $param['limit'] = $param['limit'] ?? '';
        $res = MessageAction::history($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 最新消息
    public function lastest(array $param)
    {
        $param['friend_id'] = $param['friend_id'] ?? '';
        $param['limit_id'] = $param['limit_id'] ?? '';
        $res = MessageAction::lastest($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 重置未读数量
    public function resetUnread(array $param)
    {
        $param['friend_id'] = $param['friend_id'] ?? '';
        $res = MessageAction::resetUnread($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 删除消息
    public function delete(array $param)
    {
        $param['message_id'] = $param['message_id'] ?? '';
        $res = MessageAction::delete($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 设置阅后即焚消息已读未读
    public function readedForBurn(array $param)
    {
        $param['message_id'] = $param['message_id'] ?? '';
        $res = MessageAction::readedForBurn($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 设置单条消息已读/未读（所有类型消息）
    public function readed(array $param)
    {
        $param['message_id'] = $param['message_id'] ?? '';
        $res = MessageAction::readed($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }


    // 消息撤回
    public function withdraw(array $param)
    {
        $param['message_id'] = $param['message_id'] ?? '';
        $res = MessageAction::withdraw($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 消息转发-逐条转发
    public function serialForward(array $param)
    {
        $param['type'] = $param['type'] ?? '';
        $param['target_id'] = $param['target_id'] ?? '';
        $param['message_id'] = $param['message_id'] ?? '';
        $res = MessageAction::serialForward($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 消息转发-合并转发
    public function mergeForward(array $param)
    {
        $param['type'] = $param['type'] ?? '';
        $param['target_id'] = $param['target_id'] ?? '';
        $param['message_id'] = $param['message_id'] ?? '';
        $res = MessageAction::mergeForward($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 私聊消息-同步（用于同步app本地数据库和线上数据库）
    public function sync(array $param)
    {
        $param['id_list'] = $param['id_list'] ?? '';
        $res = MessageAction::sync($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 私聊消息-同步（用于同步app本地数据库和线上数据库）
    public function syncForSingle(array $param)
    {
        $param['message_id'] = $param['message_id'] ?? '';
        $res = MessageAction::syncForSingle($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }


}