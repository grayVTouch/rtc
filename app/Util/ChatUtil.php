<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/11
 * Time: 11:19
 */

namespace App\Util;


class ChatUtil
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
}