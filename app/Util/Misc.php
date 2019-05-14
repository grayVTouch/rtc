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
    public static function uniqueCode()
    {
        return random(256 , 'mixed' , true);
    }
}