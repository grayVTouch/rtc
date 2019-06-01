<?php

namespace App\Util;

use function core\random;

/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 9:30
 */

class Misc
{
    // 唯一码
    public static function uniqueCode()
    {
        return random(255 , 'mixed' , true);
    }

    // 会话ID（群聊|私聊）
    public static function sessionId(string $type = '' , int $id = 0)
    {
        return md5(sprintf('%s_%d' , $type , $id));
    }
}