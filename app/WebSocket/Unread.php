<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/20
 * Time: 13:52
 */

namespace App\WebSocket;


use App\WebSocket\Action\UnreadAction;

class Unread extends Auth
{
    // 所有未读消息总量
    public function unread(array $param)
    {
        $res = UnreadAction::unread($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 所有未读消息总量
    public function unreadForSession(array $param)
    {
        $res = UnreadAction::unreadForSession($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 所有未读消息总量
    public function unreadForApp(array $param)
    {
        $res = UnreadAction::unreadForApp($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 重置所有消息为已读消息
    public function resetSessionUnread(array $param)
    {
        $res = UnreadAction::resetSessionUnread($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }
}