<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/14
 * Time: 23:37
 */

namespace App\WebSocket;

use App\WebSocket\Action\ChatAction;

class Chat extends Auth
{
    // 平台咨询
    public function advoise(array $param)
    {
        $param['user_id'] = $this->user->id;
        $param['group_id'] = $param['group_id'] ?? '';
        $param['type'] = $param['type'] ?? '';
        $param['message'] = $param['message'] ?? '';
        $param['extra'] = $param['extra'] ?? '';
        $res = ChatAction::advoise($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);

    }
}