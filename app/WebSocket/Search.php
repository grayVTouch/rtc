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
}