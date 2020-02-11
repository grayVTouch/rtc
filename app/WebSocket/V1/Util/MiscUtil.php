<?php

namespace App\WebSocket\V1\Util;

use function core\random;
use function core\ssl_random;

/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 9:30
 */

class MiscUtil
{
    // 唯一码
    public static function uniqueCode()
    {
        return random(255 , 'mixed' , true);
    }

    // 会话ID（群聊|私聊）
    public static function sessionId(string $type = '' , $id = 0)
    {
        return md5(sprintf('%s_%s' , $type , $id));
    }

    // 生成标识符
    public static function identifier()
    {
        return ssl_random(32);
    }

    // 生成 token
    public static function token()
    {
        return ssl_random(255);
    }

}