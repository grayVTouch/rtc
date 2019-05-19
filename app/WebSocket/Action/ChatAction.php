<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 10:21
 */

namespace App\WebSocket\Action;


class ChatAction extends Action
{
    // 私聊
    public static function _private($msg_id)
    {

    }

    // 群聊
    public static function group($msg_id)
    {

    }

    // 会话列表
    public static function util_session($user_id)
    {
        // 获取所有的私聊会话
        // 获取所有的群聊会话
    }
}