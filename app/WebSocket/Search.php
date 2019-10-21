<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/12
 * Time: 13:54
 */

namespace App\WebSocket;


use App\WebSocket\Action\SearchAction;

class Search extends Auth
{
    // 全网搜索
    public function searchInNet(array $param)
    {
        $param['value'] = $param['value'] ?? '';
        $res = SearchAction::searchInNet($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 本地搜索
    public function searchInLocal(array $param)
    {
        $param['value'] = $param['value'] ?? '';
        $res = SearchAction::searchInLocal($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 本地搜索-完整的好友列表
    public function searchForFriendInLocal(array $param)
    {
        $param['value'] = $param['value'] ?? '';
        $res = SearchAction::searchForFriendInLocal($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 本地搜索-完整的群列表
    public function searchForGroupInLocal(array $param)
    {
        $param['value'] = $param['value'] ?? '';
        $res = SearchAction::searchForGroupInLocal($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 本地搜索-完整的会话记录（包含检索字符串）
    public function searchForSessionInLocal(array $param)
    {
        $param['value'] = $param['value'] ?? '';
        $res = SearchAction::searchForSessionInLocal($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 本地搜索-私聊-单个会话通道的聊天记录
    public function searchForPrivateHistoryInLocal(array $param)
    {
        $param['chat_id'] = $param['chat_id'] ?? '';
        $param['value'] = $param['value'] ?? '';
        $param['limit_id'] = $param['limit_id'] ?? '';
        $param['limit'] = $param['limit'] ?? '';
        $res = SearchAction::searchForPrivateHistoryInLocal($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }

    // 本地搜索-群聊-单个会话通道的聊天记录
    public function searchForGroupHistoryInLocal(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $param['value'] = $param['value'] ?? '';
        $param['limit_id'] = $param['limit_id'] ?? '';
        $param['limit'] = $param['limit'] ?? '';
        $res = SearchAction::searchForGroupHistoryInLocal($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }
}