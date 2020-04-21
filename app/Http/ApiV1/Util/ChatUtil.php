<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/11
 * Time: 11:19
 */

namespace App\Http\ApiV1\Util;


class ChatUtil extends Util
{
    /**
     * 生成会话id
     *
     * @param int $sender 发送者id
     * @param int $receiver 接收者id
     * @return string
     */
    public static function chatId(int $sender , int $receiver): string
    {
        $min = min($sender , $receiver);
        $max = max($sender , $receiver);
        return sprintf('%d_%d' , $min , $max);
    }

    // 会话ID（群聊|私聊）
    public static function sessionId(string $type = '' , $id = 0)
    {
        return md5(sprintf('%s_%s' , $type , $id));
    }

}